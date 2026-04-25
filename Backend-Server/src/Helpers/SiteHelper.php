<?php

namespace App\Helpers;

use Symfony\Component\Uid\Uuid;

class SiteHelper
{

    public function isPropertyUpdated(array $data, $property, $oldValue): bool
    {
        $updated = false;
        if (array_key_exists($property, $data)) {
            $updated = $data[$property] != $oldValue;
        }

        return $updated;
    }

    public function isNestedPropertyUpdated(array $data, $propertyPath, $oldValue): bool
    {
        $tokens = explode(".", $propertyPath);

        foreach ($tokens as $token) {
            $isExists = array_key_exists($token, $data);
            if (!$isExists) return false;
            $data = $data[$token];
        }
        return $data != $oldValue;
    }

    public static function generateSlug(string $prefix): string
    {
        return $prefix . '-' . Uuid::v4();
    }

    // STRING HELPERS
    public static function generateRandomString($length = 7, $capital = false): string
    {
        if ($capital)
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        else
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @throws \Exception
     */
    public static function getDateBefore(string $period): \DateTime
    {
        $now = new \DateTime();

        $interval = new \DateInterval($period);

        return $now->sub($interval);
    }

    /*
     this function is reformat seeds format when you
     export the dataBase from database management System the format is not accepted on addSql function so use this to
     reformat query and upload that to dataBas
     */
    private function formatSqlFilesAndUpload($filePath)
    {

        $file = realpath($filePath);

        $handle = fopen($file, "r");
        $data = '';
        // this section i for format data as sql query
        while (($line = fgets($handle)) !== false) {
            $line = str_replace("\r\n", '', $line);
            $line = str_replace("\r", '', $line);
            $line = str_replace("\n", '', $line);
            $line = str_replace("INSERT", "\nINSERT", $line);
            if ($line) {
                $data.= $line;
            }
        }
        fclose($handle);
        //this section to clear data
        $handle = fopen($file, 'w');
        fclose($handle);

        // this section to upload data on file
        $handle = fopen($file, 'w');
        fwrite($handle, $data);
        fclose($handle);
        //this section to migrate seed on dataBase
        $this->addSql($data);



    }


}