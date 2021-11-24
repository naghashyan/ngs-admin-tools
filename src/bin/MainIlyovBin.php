<?php
$_dir = str_replace("/bin", "", __DIR__);
$_dir = str_replace("\bin", "", $_dir);
chdir(__DIR__ . '/../../../htdocs');

$options = [];
if (count($argv) <= 0){
  return;
}
for ($i = 1; $i < count($argv); $i++){
  $option = $argv[$i];
  if ($option[0] != "-"){
    echo "\n \033[0;31m option parameter should start '-' \033[0m \n";
    exit;
  }
  $option = substr($option, 1);
  if (strpos($option, "=") === false){
    $options[$option] = true;
    continue;
  }

  preg_match("/(.*)=(.*)/i", $option, $matchedArr);
  $options[$matchedArr[1]] = $matchedArr[2];
}
$_SERVER["ENVIRONMENT"] = "production";
if (isset($options["env"])){
  $_SERVER["ENVIRONMENT"] = $options["env"];
}
require_once("../vendor/autoload.php");
NGS()->define("CMD", true);
if (isset($options["version"]) || isset($options["v"])){
  cliLog("Copyright (c) 2010-" . date("Y") . " NGS");
  cliLog("ENVIRONMENT: " . NGS()->getEnvironment() . ". version " . NGS()->get("VERSION"));
  exit;
}
\ngs\util\NgsArgs::getInstance()->setArgs($options);
function cliLog($log, $color = "white", $bold = false) {
  $colorArr = ["black" => "30", "blue" => "34", "green" => "32", "cyan" => "36",
    "red" => "31", "purple" => "35", "prown" => "33", "light_gray" => "37 ",
    "gark_gray" => "30", "light_blue" => "34", "light_green" => "32", "light_cyan" => "36",
    "light_red" => "31", "light_purple" => "35", "yellow" => "33", "white" => "40"];
  $colorCode = $colorArr["white"];
  if ($colorArr[$color]){
    $colorCode = $colorArr[$color];
  }
  if ($bold){
    $colorCode = "1;" . $colorCode;
  } else{
    $colorCode = "0;".$colorCode;
  }
  echo "\033[" . $colorCode . "m" . $log . "  \033[0;" . $colorArr["white"] . "m \n";
}