<?php
//******************************************************************************************************************
//*												                                                          
//*					               This SOURCE CODE is part of                                           
//*												                                                          
//*                           TEST SYMFONY 4, TRANSFER DATAS FROM A FILE XML TO DATABASE                         
//*
//*                                THIS WORK is MAKED By 
//*                                     MEJRI MOHAMED
//*
//******************************************************************************************************************
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
class OrdersFromXmlToDatabaseCommand extends Command
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }
    protected function configure() {
        $this
                ->setName('order:xmltodatabase:send')
                ->setDescription('This command open a file xml, extract datas and send it to a table in database.')
                ->setHelp("This command allows you to synchronise data orders from a xml file with a database.")
        ;
    }

    private function loadXmlFile($path, $name) {
        if (!$xml = \simplexml_load_file($path . $name . '.xml','SimpleXMLElement', LIBXML_NOCDATA))
            return false;
        return $xml;
    }

    function execute(InputInterface $input, OutputInterface $output) {
        $path = $this->container->getParameter('xml_path');
        $name = $this->container->getParameter('xml_name');
        $xml = $this->loadXmlFile($path ,$name );
        $xmlJson = json_encode($xml);
        $xmlArr = json_decode($xmlJson, 1);
        $em = $this->container->get('doctrine')->getManager();
        foreach($xmlArr["orders"] as $order)
        {
            foreach($order as $ord) {
                $object = new Order();
                $object->setAmount(floatval($ord["order_amount"]));
                $object->setTax(floatval($ord["order_tax"]));
                $object->setShipping(floatval($ord["order_shipping"]));
                $object->setCurrency($ord["order_currency"]);
                $em->persist($object);
                $em->flush();
            }
        }

    }

}
