<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class WeeklyReport extends Module
{
    public function __construct()
    {
        $this->name = weeklyreport';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Jonas Hjelm';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Weekly Report');
        $this->description = $this->l('Generates a weekly report file for specified products.');
    }

    public function install()
    {
        // Add both parent::install() and the hook registration
        return parent::install() 
            && $this->registerHook('actionRegisterSymfonyCommands')
            // Add module configuration if needed
            && $this->installConfiguration();
    }

    public function uninstall()
    {
        // Clean up any module configuration if needed
        return $this->uninstallConfiguration() 
            && parent::uninstall();
    }

    protected function installConfiguration()
    {
        // Add any configuration values if needed
        return true;
    }

    protected function uninstallConfiguration()
    {
        // Remove any configuration values if needed
        return true;
    }
    
    public function hookActionRegisterSymfonyCommands($params)
    {
        if (!isset($params['router'])) {
            return;
        }

        try {
            // Get the service container
            $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
            if (!$container) {
                return;
            }

            // Register the command service
            if (!$container->has('weeklyreport.command.generate_report')) {
                return;
            }

            // Add the command to the router collection
            $collection = $params['router']->getRouteCollection();
            $params['router']->addCollection($collection);

        } catch (\Exception $e) {
            PrestaShopLogger::addLog(
                'WeeklyReport: Error registering commands - ' . $e->getMessage(),
                3 // Error severity
            );
            return false;
        }
    }
}
