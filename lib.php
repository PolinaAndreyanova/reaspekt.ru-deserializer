<?php
function varDump(array $arData): void
{
    echo "<pre>";
    var_dump($arData);
    echo "</pre>";
}

function printR(array $arData): void
{
    echo "<pre>";
    print_r($arData);
    echo "</pre>";
}

function handlerSerializedString(string $string): array
{
    $arString = str_split($string);
    $arDataSplit = [];
    $isChange = false;
    $isAddType = false;
    $c = 0;

    foreach ($arString as $k => $v) {
        if ($v == "O" and $arString[$k + 1] == ":") {
            $isChange = !$isChange;

            $arDataSplit[] = "a";
            $arDataSplit[] = ":";
            $arDataSplit[] = "1";
            $arDataSplit[] = ":";
            $arDataSplit[] = "{";
            $arDataSplit[] = "s";

            $isAddType = true;
            $c = 0;
        } elseif ($v == ":" and $arString[$k - 1] == '"' and $isChange) {
            $isChange = !$isChange;

            $arDataSplit[] = ";";
            $arDataSplit[] = "a";
            $arDataSplit[] = ":";
        } else {
            $arDataSplit[] = $v;
            
            if ($isAddType and $v === ":") {
                $c += 1;
            } elseif ($isAddType and $v === '"' and $c === 2) {
                $arDataSplit[] = "O";
                $arDataSplit[] = "B";
                $arDataSplit[] = "J";
                $arDataSplit[] = "E";
                $arDataSplit[] = "C";
                $arDataSplit[] = "T";
                $arDataSplit[] = "_";

                $isAddType = false;
                $c = 0;
            } 
        }
    }

    return $arDataSplit;
}

function deserialize(string $data, $curDataType = "", $newKey = "", $arCurDataValueSplit = [], $isOpenBracket = false, $isCount = false, $countArElems = [], $isEmptyArray = false): array
{
    $arDeserializedData = [];
    $arDataSplit = handlerSerializedString($data);

    // $curLevel = &$arDeserializedData;
    // $arLevels = [];

    // $curDataType = "";
    // $newKey = "";
    // $arCurDataValueSplit = [];
    
    // $isOpenBracket = false;
    
    // $isCount = false;
    // $countArElems = [];

    // $isEmptyArray = false;

    foreach ($arDataSplit as $number => $symbol) {
        if ($isOpenBracket) {
            $arCurDataValueSplit[] = $symbol;

            if ($symbol === ")") {
                $isOpenBracket = !$isOpenBracket;
            }
        } else {
            if (in_array($symbol, ["s", "i", "d"]) and $arDataSplit[$number + 1] === ":") {
                $curDataType = $symbol;
                $arCurDataValueSplit[] = $symbol;
            } elseif ($symbol === "a" and $arDataSplit[$number + 1] === ":") {
                $curDataType = $symbol;
                $arCurDataValueSplit[] = $symbol;
                
                if ($arDataSplit[$number + 2] === "0") {
                    $isEmptyArray = !$isEmptyArray;
                } else {
                    // $arLevels[] = &$curLevel;
                    // $curLevel = &$curLevel[$newKey];

                    $countArElems[] = 0;
                    $arDeserializedData[$newKey] = deserialize(substr($data, $number + 2), $curDataType, "", $arCurDataValueSplit, $isOpenBracket, $isCount, $countArElems, $isEmptyArray);

                    // $newKey = "";
                    
                    // $countArElems[] = 0;
                }
            } elseif ($symbol === ":") {
                if (
                    ($arDataSplit[$number + 1] === "/" and $arDataSplit[$number + 2] === "/")
                    or in_array($arDataSplit[$number + 1], ["=", ";"])
                ) {
                    $arCurDataValueSplit[] = $symbol;
                } elseif (in_array($curDataType, ["s", "i", "d"])) {
                    $arCurDataValueSplit[] = $symbol;
                } else {
                    $arCurDataValueSplit[] = $symbol;
                    
                    if ($arDataSplit[$number - 1] === "a" and $arDataSplit[$number + 1] !== "0") {
                        $isCount = true;
                    } else {
                        $isCount = false;
                    }
                }
            } elseif ($symbol === ";" or ($isEmptyArray and $symbol === "}")) {
                if (
                    !(in_array($arDataSplit[$number + 1], ["a", "s", "i", "d", "N", "}"]) 
                    or (!isset($arDataSplit[$number + 1])))
                ) {
                    $arCurDataValueSplit[] = $symbol;
                } else {
                    $curValue = implode($arCurDataValueSplit);

                    $arCurDataValueSplit = [];

                    $arCurValue = [];

                    if ($curDataType === "s") {
                        $arCurValue = preg_split("/s:[0-9]*:/", $curValue);
                    } elseif (in_array($curDataType, ["i", "d"])) {
                        $arCurValue = preg_split("/[id]:/", $curValue);
                    } elseif ($isEmptyArray) {
                        $curValue[-1] = "[";
                        $curValue .= "]";                        

                        $arCurValue = preg_split("/a:0:/", $curValue);

                        $isEmptyArray = !$isEmptyArray;
                    }
                    
                    $curDataType = "";
                    
                    if ($newKey === "") {
                        $newKey = $arCurValue[array_key_last($arCurValue)];
                        
                        $curLevel[$newKey] = [];
                    } else {
                        $curLevel[$newKey] = $arCurValue[array_key_last($arCurValue)];
                        
                        $newKey = "";
                        
                        $countArElems[array_key_last($countArElems)] -= 1;
                        
                        while ($countArElems[array_key_last($countArElems)] === 0) {
                            // $curLevel = &$arLevels[array_key_last($arLevels)];
                            
                            // unset($arLevels[array_key_last($arLevels)]);
                            unset($countArElems[array_key_last($countArElems)]);
                            
                            if ($countArElems[array_key_last($countArElems)] >= 1) {
                                $countArElems[array_key_last($countArElems)] -= 1;
                            }
                        }
                    }
                }
            } elseif (in_array($symbol, ["(", ")"])) {
                $isOpenBracket = !$isOpenBracket;
                
                $arCurDataValueSplit[] = $symbol;
            } else {
                if ($symbol === "N" and $arDataSplit[$number + 1] === ";") {
                    $curDataType = "s";
                    $arCurDataValueSplit = ["s", ":", "1", ":"];    
                }
                
                $arCurDataValueSplit[] = $symbol;
                
                if ($isCount) {
                    $countArElems[array_key_last($countArElems)] = $countArElems[array_key_last($countArElems)] * 10 + intval($symbol);
                }
            }
        }
    }

    return $arDeserializedData;
}
