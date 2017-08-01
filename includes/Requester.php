<?php

class Requester
{
    public function launch($entityId, $token, $environement)
    {
        if (!is_callable('curl_init')) {
            Logger::error('Curl no exist, request impossible..');
            header('HTTP/1.0 501 Not Implemented');

            exit('Curl request impossible for wordpress server');
        }

        $curl = curl_init();

        $envtype = $_ENV['URL_SOCIALLYMAP'];
        $envtype = $envtype[$environement];

        if (empty($envtype)) {
            $envtype = 'http://app.sociallymap.com';
        }

        $urlCreator = [
            'baseUrl' => $envtype,
            'entityId'=> $entityId,
            'token'   => $token,
        ];


        $targetUrl = $urlCreator['baseUrl'].'/raw-exporter/'.$urlCreator['entityId'].
        '/feed?token='.$urlCreator['token'];

        Logger::info('Request CURL at ' . $targetUrl);

        $options = [
            // Return the transfer, don't display it
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_URL            => $targetUrl,
            CURLOPT_FOLLOWLOCATION => true
        ];

        curl_setopt_array($curl, $options);

        $result = curl_exec($curl);
        $requestInfos = curl_getinfo($curl);


        // Close the curl session and free allocated memory
        curl_close($curl);

        if ($_ENV['environnement'] == 'dev' || $_ENV['environnement'] == 'debug') {
            Logger::info('Result of request : ' . $result);
        }

        try {
            if (!$result) {
                throw new Exception('Wrong response format');
            }

            // Decode the JSON response
            $result = json_decode($result);

            // The request failed
            if ($requestInfos['http_code'] !== 200) {
                throw new Exception($result->message);
            }
        } catch (Exception $exception) {
            header('HTTP/1.0 502 Bad Gateway');
            Logger::error($exception->getMessage());

            exit;
        }


        return $result;
    }
}
