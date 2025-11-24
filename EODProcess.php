<?php

/**
 * RoyalMailShipping by StreamMarket
 *
 * @category    StreamMarket
 * @package StreamMarket_RoyalMailShipping
 * @author  Product Development Team <support@StreamMarket.co.uk>
 * @license http://extensions.StreamMarket.co.uk/license
 *
 */

namespace StreamMarket\RoyalMailShipping\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EODProcess extends Command
{
   protected function configure()
   {
       $this->setName('shipment:massManifest');
       $this->setDescription('Demo command line');
       
       parent::configure();
   }
   
   protected function execute(InputInterface $input, OutputInterface $output)
   {
       $curl = curl_init();

		curl_setopt_array($curl, [
		  CURLOPT_URL => "https://api.royalmail.net/shipping/v3/manifests",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\"PostingLocation\":\"9000446029\"}",
		  CURLOPT_HTTPHEADER => [
			"X-IBM-Client-Id: d62ae68b-1797-4c84-a6d4-e80d87cf9993",
			"X-RMG-Auth-Token: eyJraWQiOiJoczI1Ni1rZXkiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE2MzYzNjY5NDIsImlhdCI6MTYzNjM1MjU0MiwidXNlcklkIjoiMDQxODI5MTAwMEFQSSIsInBhc3N3b3JkIjoiamxsMTBSeGE2UmlOSGdBUml1RWc3dE5OSHBNPSJ9.9d0CeU9V-D2KUbCFh_Zsum7aSWv8qbGV7pl9SUIAvDg",
			"accept: application/json",
			"content-type: application/json"
		  ],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
   }
   
   
   
}
