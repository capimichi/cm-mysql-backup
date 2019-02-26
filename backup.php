<?php

use CmMysqlBackup\Helper\DirectoryHelper;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$configPath = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.yaml";

$config = Yaml::parseFile($configPath);

$command = "{mysqldump_path} --opt -h {db_host} -P{db_port} -u{db_user} -p{db_password} {db_name} | gzip > {output}";

foreach ($config['db_names'] as $dbName) {
    
    $dbCommand = $command;
    
    $outputDir = $config['backup_dir'] . DIRECTORY_SEPARATOR . date("Ymd") . DIRECTORY_SEPARATOR . $dbName;
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    $outputPath = $outputDir . DIRECTORY_SEPARATOR . date("Ymdhis") . "_" . $dbName . ".sql.gz";
    
    $dbCommand = str_replace("{mysqldump_path}", $config['mysqldump_path'], $dbCommand);
    $dbCommand = str_replace("{db_host}", $config['db_host'], $dbCommand);
    $dbCommand = str_replace("{db_port}", $config['db_port'], $dbCommand);
    $dbCommand = str_replace("{db_user}", $config['db_user'], $dbCommand);
    $dbCommand = str_replace("{db_password}", $config['db_password'], $dbCommand);
    $dbCommand = str_replace("{db_name}", $dbName, $dbCommand);
    $dbCommand = str_replace("{output}", $outputPath, $dbCommand);
    
    exec($dbCommand);
}

$directories = array_diff(scandir($config['backup_dir']), ['..', '.', ".DS_Store"]);

foreach ($directories as $directory) {
    
    $date = DateTime::createFromFormat("Ymd", $directory);
    
    if ($date) {
        $today = new DateTime();
        $difference = $today->getTimestamp() - $date->getTimestamp();
        $differenceDays = round($difference / (60 * 60 * 24));
        if ($differenceDays >= $config['max_days']) {
            $directoryFullPath = $config['backup_dir'] . DIRECTORY_SEPARATOR . $directory;
            if (file_exists($directoryFullPath)) {
                DirectoryHelper::delete($directoryFullPath);
            }
        }
    }
    
}