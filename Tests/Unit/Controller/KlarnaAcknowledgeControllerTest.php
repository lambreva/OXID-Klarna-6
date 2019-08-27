<?php

namespace TopConcepts\Klarna\Tests\Unit\Controller;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Field;
use TopConcepts\Klarna\Controller\KlarnaAcknowledgeController;
use TopConcepts\Klarna\Core\KlarnaOrderManagementClient;
use TopConcepts\Klarna\Tests\Unit\ModuleUnitTestCase;

class KlarnaAcknowledgeControllerTest extends ModuleUnitTestCase {

    public function testInit() {
        $controller = new KlarnaAcknowledgeController();
        $result = $controller->init();
        $this->assertNull($result);

        $this->setRequestParameter('klarna_order_id', '16302e97f6249f2babcdef65004954b1');
        $controller->init();
        $order = $this->getMockBuilder(Order::class)->setMethods(['isLoaded'])->getMock()
            ->expects($this->once())->method->willReturn(true);
        $order->oxorder__oxbillcountryid = new Field('111', Field::T_RAW);
        $client = $this->getMockBuilder(KlarnaOrderManagementClient::class)
            ->setMethods(['acknowledgeOrder', 'cancelOrder'])->getMock();
        $client->expects($this->once())->method('acknowledgeOrder')->willReturn(true);
        $client->expects($this->once())->method('cancelOrder')->willReturn(true);
        $controller = $this->getMockBuilder(KlarnaAcknowledgeController::class)
            ->setMethods(['loadOrderByKlarnaId', 'getKlarnaClient'])->getMock();
        $controller->expects($this->once())->method('loadOrderByKlarnaId')->willReturn($order);
        $controller->expects($this->once())->method('getKlarnaClient')->willReturn($client);
        $controller->init();

        $order = $this->getMockBuilder(Order::class)->setMethods(['isLoaded'])->getMock();
        $order->expects($this->once())->method('isLoaded')->will($this->throwException(new StandardException()));
        $controller = $this->getMockBuilder(KlarnaAcknowledgeController::class)
            ->setMethods(['loadOrderByKlarnaId', 'getKlarnaClient'])->getMock();
        $controller->expects($this->once())->method('loadOrderByKlarnaId')->willReturn($order);
        $controller->expects($this->once())->method('getKlarnaClient')->willReturn($client);
        $result = $controller->init();
        $this->assertLoggedException(StandardException::class, '');
        $this->assertNull($result);
    }
}
