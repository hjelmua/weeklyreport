<?php

namespace MyWeeklyReport\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use Db;

class MyWeeklyReportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('myweeklyreport:generate')
            ->setDescription('Generate weekly report for specified products')
            ->addArgument('references', InputArgument::IS_ARRAY, 'Product references to include in the report');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $references = $input->getArgument('references');
        
        $context = SymfonyContainer::getInstance()->get('prestashop.adapter.legacy.context')->getContext();
        $products = [];

        foreach ($references as $reference) {
            // Get product ID from reference
            $productId = $this->getProductIdByReference($reference);
            if ($productId) {
                $product = new \Product($productId, true, $context->language->id);
                if (\Validate::isLoadedObject($product)) {
                    $products[] = $this->getProductData($product, $context);
                    $output->writeln("Found product with reference: $reference");
                }
            } else {
                $output->writeln("<error>Product not found with reference: $reference</error>");
            }
        }

        if (!empty($products)) {
            $this->generateReport($products, $output);
            return Command::SUCCESS;
        }

        $output->writeln("<error>No valid products found</error>");
        return Command::FAILURE;
    }

    private function getProductIdByReference($reference)
    {
        $sql = 'SELECT id_product FROM ' . _DB_PREFIX_ . 'product WHERE reference = "' . pSQL($reference) . '"';
        return Db::getInstance()->getValue($sql);
    }

    private function getProductData($product, $context)
    {
        $link = $context->link;
        $currency = $context->currency;

        $productData = [
            'id_product' => $product->id,
            'reference' => $product->reference,
            'cover_url' => $link->getImageLink($product->link_rewrite, $product->getCover()['id_image'], 'home_default2x'),
            'url' => $link->getProductLink($product),
            'price' => \Tools::displayPrice(\Product::getPriceStatic($product->id)),
            'has_discount' => $product->specificPrice && $product->specificPrice['reduction'] > 0,
            'regular_price' => \Tools::displayPrice(\Product::getPriceStatic($product->id, false, null, 6, null, false, false)),
        ];

        if ($productData['has_discount']) {
            $productData['discount_type'] = $product->specificPrice['reduction_type'];
            $productData['discount_percentage'] = '-' . round((1 - $product->specificPrice['reduction']) * 100) . '%';
        }

        return $productData;
    }

    private function generateReport($products, OutputInterface $output)
    {
        $smarty = SymfonyContainer::getInstance()->get('prestashop.adapter.legacy.context')->getContext()->smarty;
        $smarty->assign('products', $products);

        $content = $smarty->fetch(_PS_MODULE_DIR_ . 'myweeklyreport/views/templates/report.tpl');

        $filename = 'weekly_report_' . date('Y-m-d') . '.html';
        file_put_contents(_PS_ROOT_DIR_ . '/var/logs/' . $filename, $content);

        $output->writeln("Report generated: $filename");
    }
}
