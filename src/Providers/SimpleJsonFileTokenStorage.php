<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 4/29/2015
 * Time: 4:56 PM
 */
namespace NovakSolutions\Infusionsoft\Providers;

class SimpleJsonFileTokenStorage implements TokenStorageProvider{
    public $fileName = '';

    public function __construct($fileName = 'infusionsoft-tokens.php'){
        $this->fileName = $fileName;
    }

    public function saveTokens($appDomainName, $accessToken, $refreshToken, $expiresIn){
        $data = $this->readFile();

        $data[$appDomainName] = array(
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expiresAt' => time() + $expiresIn
        );

        file_put_contents($this->fileName, "<?php\n" . json_encode($data));
    }

    public function getTokens($appDomainName){
        $data = $this->readFile();
        if(isset($data[$appDomainName])){
            return $data[$appDomainName];
        } else {
            return array(
                'accessToken' => '',
                'refreshToken' => '',
                'expiresAt' => 0
            );
        }
    }

    public function readFile(){
        if (file_exists($this->fileName)) {
            $fileContents = file_get_contents($this->fileName);
            $fileContents = substr($fileContents, 5);
            $data = json_decode($fileContents, true);
            return $data;
        } else {
            $data = array();
            return $data;
        }
    }
}