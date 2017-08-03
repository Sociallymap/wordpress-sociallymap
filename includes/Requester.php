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
            $envtype = 'https://app.sociallymap.com';
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
            CURLOPT_URL            => $targetUrl,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        $curl_error = curl_error($curl);
        $requestInfos = curl_getinfo($curl);

        curl_close($curl);

        if ($_ENV['environnement'] == 'dev' || $_ENV['environnement'] == 'debug') {
            Logger::info('Result of request : ' . $response);
        }

        try {
            if (!$response) {
                $errorMessage = sprintf(
                    'Empty response with error #%s : %s',
                    $curl_errno,
                    $curl_error
                );

                throw new Exception($errorMessage);
            }

            // Decode the JSON response
            $jsonResponse = json_decode($response);

            // The request failed
            if ($requestInfos['http_code'] !== 200) {
                throw new Exception($jsonResponse->message);
            }
        } catch (Exception $exception) {
            header('HTTP/1.0 502 Bad Gateway');
            Logger::error($exception->getMessage());

            exit;
        }

        return $jsonResponse;
    }
}
