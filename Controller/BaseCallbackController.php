<?php


namespace TopConcepts\Klarna\Controller;


use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\Registry;
use TopConcepts\Klarna\Core\KlarnaLogs;

abstract class BaseCallbackController extends BaseController
{
    protected $defaultActionRules = [
        'log' => false,
        'validator' => []
    ];

    protected $actionRules = [];

    /** @var array request body */
    protected $requestData;

    /** @var array  */
    protected $actionData = [];

    public function init() {
        parent::init();
        $this->requestData = $this->getRequestData();
        Registry::getLogger()->log('info', $this->getFncName() . " request:\n" , $this->requestData);
        if ($this->validateRequestData() === false) {
            die('Forbidden');
        }
    }

    /**
     * Logs callback request
     * Sends response
     * @return string|void
     */
    public function render() {
        $loggingEnabeld = Registry::getConfig()->getConfigParam('blKlarnaLoggingEnabled');
        if($loggingEnabeld && $this->getActionRules('log')) {
            $oLog = new KlarnaLogs();
            $oLog->logData(
                $this->getFncName(),
                $this->requestData,
                'callback',
                '',
                '',
                0
            );
        }
        exit;
        // render response;
    }


    protected function getRequestData()
    {
        $body = file_get_contents("php://input");

        return (array)json_decode($body, true);
    }

    protected function validateRequestData()
    {
        $checkRule = function($fieldName, $attribute) {
            switch($attribute) {
                case 'required':
                    return isset($fieldName, $this->requestData);

                case 'notEmpty':
                    return empty($this->requestData[$fieldName]) === false;

                case 'extract':
                    $this->actionData[$fieldName] = $this->requestData[$fieldName];
                    return true;
            }
        };

        if ($actionRules = $this->getActionRules()) {
            $rules = array_merge(
                $this->defaultActionRules,
                $actionRules
            );
            foreach($rules['validator'] as $fieldName => $attributes) {
                foreach ($attributes as $attribute) {
                    if ($checkRule($fieldName, $attribute) === false) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    protected function getActionRules($ruleName = null) {
        if (isset($this->actionRules[$this->getFncName()])) {
            $rules = $this->actionRules[$this->getFncName()];
            if ($ruleName) {
                 return isset($rules[$ruleName]) ? $rules[$ruleName] : null;
            }
            return $rules;
        }
    }
}