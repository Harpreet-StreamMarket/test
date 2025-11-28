<?php
namespace StreamMarket\RoyalMailShipping\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use StreamMarket\RoyalMailShipping\Helper\Data as HelperData;

class EODProcess extends Command
{
	const XML_PATH_CLIENT_ID = 'carriers/smroyalmail/client_id';
	
    protected $helperData;
	protected $scopeConfig;

    public function __construct(
        HelperData $helperData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helperData = $helperData;
		$this->scopeConfig = $scopeConfig;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('shipment:massManifest');
        $this->setDescription('EOD Process');
        parent::configure();
    }
	
	public function getSystemConfigValue($system_code){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			return $this->scopeConfig->getValue($system_code, $storeScope);
		}

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $this->helperData->generateToken();
		$client_id = $this->getSystemConfigValue(self::XML_PATH_CLIENT_ID);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.royalmail.net/shipping/v3/manifests",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "X-IBM-Client-Id: $client_id",
                "X-RMG-Auth-Token: $token",
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $output->writeln("cURL Error #: " . $err);
        } else {
            $output->writeln($response);
        }
    }
}
