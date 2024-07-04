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

function deserialize(string $data): array
{
    // $arObjects = preg_split("/O:[0-9]*:/", $data);
    $arDeserializedData = [];

    // foreach ($arObjects as $object) {
    //     if (!($object === "")) {
            // $arObjectData = explode(":", $object, 3);

            // $objectName = $arObjectData[0];
            // $countProp = $arObjectData[1];

            $preobrazovanie = str_split($data);
            $arObjectPropSplit = [];
            $isMeniaem = false;
            foreach ($preobrazovanie as $k => $v) {
                if ($v == "O" and $preobrazovanie[$k + 1] == ":") {
                    $isMeniaem = !$isMeniaem;

                    $arObjectPropSplit[] = "a";
                    $arObjectPropSplit[] = ":";
                    $arObjectPropSplit[] = "1";
                    $arObjectPropSplit[] = ":";
                    $arObjectPropSplit[] = "{";
                    $arObjectPropSplit[] = "s";
                } else if ($v == ":" and $preobrazovanie[$k - 1] == '"' and $isMeniaem) {
                    $isMeniaem = !$isMeniaem;

                    $arObjectPropSplit[] = ";";
                    $arObjectPropSplit[] = "a";
                    $arObjectPropSplit[] = ":";
                } else {
                    $arObjectPropSplit[] = $v;
                }
            }

            // return $arObjectPropSplit;


            $arObjectProp = [];
            // $arObjectPropSplit = str_split($data);

            $curLevel = &$arObjectProp;
            $arLevels = [];

            $curDataType = "";
            $newKey = "";
            $arCurDataValueSplit = [];
            
            $isOpenBracket = false;
            
            $isCount = false;
            $countArElems = [];

            $isEmptyArray = false;

            foreach ($arObjectPropSplit as $number => $symbol) {
                // return [];
                if ($isOpenBracket) {
                    $arCurDataValueSplit[] = $symbol;

                    if ($symbol === ")") {
                        $isOpenBracket = !$isOpenBracket;
                    }
                } else {
                    if (in_array($symbol, ["s", "i", "d"]) and $arObjectPropSplit[$number + 1] === ":") {
                        $curDataType = $symbol;
                        $arCurDataValueSplit[] = $symbol;
                    } else if ($symbol === "a" and $arObjectPropSplit[$number + 1] === ":") {
                        $curDataType = $symbol;
                        $arCurDataValueSplit[] = $symbol;
                        
                        if ($arObjectPropSplit[$number + 2] === "0") {
                            $isEmptyArray = !$isEmptyArray;
                        } else {
                            // $curDataType = $symbol;
                            // $arCurDataValueSplit[] = $symbol;
    
                            $arLevels[] = &$curLevel;
                            $curLevel = &$curLevel[$newKey];
                            
                            $newKey = "";
                            
                            $countArElems[] = 0;
                        }
                    } else if ($symbol === ":") {
                        if (
                            ($arObjectPropSplit[$number + 1] === "/" and $arObjectPropSplit[$number + 2] === "/")
                            or in_array($arObjectPropSplit[$number + 1], ["=", ";"])
                        ) {
                            $arCurDataValueSplit[] = $symbol;
                        } else if (in_array($curDataType, ["s", "i", "d"])) {
                            $arCurDataValueSplit[] = $symbol;
                        } else {
                            $arCurDataValueSplit[] = $symbol;
                            
                            if ($arObjectPropSplit[$number - 1] === "a" and $arObjectPropSplit[$number + 1] !== "0") {
                                $isCount = true;
                                // echo implode($arCurDataValueSplit)."START<br>";
                            } else {
                                $isCount = false;
                                // echo implode($arCurDataValueSplit)."END<br>";
                            }
                        }
                    } else if ($symbol === ";" or ($isEmptyArray and $symbol === "}")) {
                        // echo implode($arCurDataValueSplit)."<br>";
                        if (
                            !(in_array($arObjectPropSplit[$number + 1], ["a", "s", "i", "d", "N", "}"]) 
                            or (!isset($arObjectPropSplit[$number + 1])))
                        ) {
                            $arCurDataValueSplit[] = $symbol;
                        } else {
                            $curValue = implode($arCurDataValueSplit);
                            
                            $arCurDataValueSplit = [];

                            $arCurValue = [];
                            
                            if ($curDataType === "s") {
                                $arCurValue = preg_split("/s:[0-9]*:/", $curValue);
                            } else if (in_array($curDataType, ["i", "d"])) {
                                $arCurValue = preg_split("/[id]:/", $curValue);
                            } else if ($isEmptyArray) {
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

                                // printR($countArElems);
                                
                                while ($countArElems[array_key_last($countArElems)] === 0) {
                                    $curLevel = &$arLevels[array_key_last($arLevels)];
                                    
                                    unset($arLevels[array_key_last($arLevels)]);
                                    unset($countArElems[array_key_last($countArElems)]);
                                    
                                    if ($countArElems[array_key_last($countArElems)] >= 1) {
                                        $countArElems[array_key_last($countArElems)] -= 1;
                                    }
                                }
                            }
                        }
                    } else if (in_array($symbol, ["(", ")"])) {
                        $isOpenBracket = !$isOpenBracket;
                        
                        $arCurDataValueSplit[] = $symbol;
                    } else {
                        if ($symbol === "N" and $arObjectPropSplit[$number + 1] === ";") {
                            $curDataType = "s";
                            $arCurDataValueSplit = ["s", ":", "1", ":"];    
                        }
                        
                        $arCurDataValueSplit[] = $symbol;
                        
                        if ($isCount) {
                            // echo $countArElems[array_key_last($countArElems)]. "+" . intval($symbol) ."<br>";
                            $countArElems[array_key_last($countArElems)] = $countArElems[array_key_last($countArElems)] * 10 + intval($symbol);
                        }
                    }
                }
            }

            // $arDeserializedData[] = [
            //     "objectName" => $objectName,
            //     "countProperties" => $countProp,
            //     "properties" => $arObjectProp
            // ];
    //     }
    // }

    // return $arDeserializedData;
    return $arObjectProp;
}
