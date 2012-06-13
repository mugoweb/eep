<?php

$csvFile = "/home/dfp/Desktop/sampledata.csv";
$hCSVFile = fopen( $csvFile, "r");

$row = 1;
if( $hCSVFile )
{
    while( ($data = fgetcsv( $hCSVFile, 1000, ",")) !== FALSE)
    {
        $num = count($data);
        echo "\n$num fields in line $row:\n";
        $row++;
        for( $c=0; $c < $num; $c++ )
        {
            echo $data[$c] . "--";
        }
    }
    fclose($hCSVFile);
}

        $mapping = array
        (
            "product_name_1"
            , "ticker_1"
            , "type_1"
            , "long_name_1"
            , "leverage_1"
            , "short_long_1"
            , "listing_exchange_1"
            , "underlying_index_1"
            , "ric_1"
            , "focus_1"
            , "asset_class_1"
            , "inception_date_1"
            , "product_description_1"
            , "benchmark_indexes_1"
            , "product_short_name_0"
            , "short_name_0"
            , "alias_0"
            , "product_primary_category_0"
            , "options_0"
            , "marginable_0"
            , "short_selling_0"
            , "issuer_0"
            , "managing_entity_0"
            , "management_fee_0"
            , "est_futures_brokerage_fee_0"
            , "expense_ratio_0"
            , "yrinv_fee_0"
            , "product_group_name_0"
            , "new_0"
            , "commission_free_0"
            , "partner_0"
            , "bony_msci_code_0"
            , "bony_equity_code_0"
            , "bony_fixed_inc_code_0"
            , "cusip_0"
            , "isin_0"
            , "product_number_0"
            , "bb_code_0"
            , "seo_title_0"
            , "seo_url_0"
            , "seo_keywords_0"
            , "pref_landing_page_0"
            , "dbiq_url_0"
            , "objective_0"
            , "mat_date_0"
            , "short_description_0"
            , "summary_0"
            , "related_funds_0"
            , "seo_description_0"
            , "related_faq_0"
            , "cust_msg_0"
        );


    
    

/* some old knowbc test about calculating usages

$maxYear = 2012;

$offset = 0;
$limit = 0; // 0 means 'no limit'

$classId = eZContentClass::classIDByIdentifier( "user");
$allUserInstances = eZContentObject::fetchSameClassList( $classId, false, $offset, $limit );

// write the heading row
echo "name,";
echo "legacy count,";
echo "bin name,";
echo "expiry date,";
echo "\"\","; // a hack to line columns up - i've lost a column and dont care to find it today
for( $year=2010; $year<=$maxYear; $year+=1 )
{
    for( $week=1; $week<=52; $week+=1 )
    {
        if( 2010 == $year && $week <= 33 ) continue;
        if( date("Y") == $year && date("W") < $week ) continue;
        
        echo "\"" . $year ."-". $week . "\",";
    }
}
echo "total";
echo "\n";

foreach( $allUserInstances as $inst )
{
    // get the full user object
    $objectId = $inst["id"];
    $object = ezContentObject::fetch( $objectId );
    $dataMap = $object->dataMap();
        
    // get the user name for display
    $userName = "\"" . $dataMap["display_name"]->DataText . "\",";
    
    // legacy count
    $legacyCount = "\"" . $dataMap["legacy_login_count"]->DataInt . "\",";
    
    // expiry date
    $expiryDate = "\"" . date( "Y-m-d", $dataMap["expiry_date"]->DataInt ) . "\",";

    // get the possibly missing user-bin name
    $binObjectId = $dataMap["user_bin"]->DataInt;
    
    $binObject = ezContentObject::fetch( $binObjectId );
    $binName = "\"\",";
    if( is_object($binObject) )
    {
        $binName = "\"" . $binObject->Name . "\",";
    }

    // get the login log
    $usageData = array();
    $params = array
    (
        "ClassFilterType" => "include"
        , "ClassFilterArray" => array( "knowbc_login_log" )
        , "Depth" => 1
        , "IgnoreVisibility" => true
        , 'Limitation' => array()
    );
    $logNodes = eZContentObjectTreeNode::subTreeByNodeID( $params, $object->MainNodeID() );
    if( 0 < count($logNodes) )
    {
        // get the serialized string of accesses
        $logObjectId = $logNodes[0]->ContentObjectID;
        $logObject = ezContentObject::fetch( $logObjectId );
        $logDataMap = $logObject->dataMap();
        $string = $logDataMap[ "log" ]->DataText;
        $usageData = unserialize( $string );
    }
    else
    {
        // got no usage data for this user
    }

    echo $userName;
    echo $legacyCount;
    echo $binName;
    echo $expiryDate;
    
    // write out usage data
    if( 0 == count( $usageData ))
    {
        // no more data, skip on
        echo "\n";
        continue;
    }

    $grandTotal = 0;
    echo "\"" . $parentName . "\"";
    for( $year=2010; $year<=$maxYear; $year+=1 )
    {
        for( $week=1; $week<=52; $week+=1 )
        {
            if( 2010 == $year && $week <= 33 ) continue;
            if( date("Y") == $year && date("W") < $week ) continue;

            $weekIndex = sprintf( "%02d", $week );
            
            $sumAccesses = 0;
            $sumAccesses += @$usageData[$year][$weekIndex]["Direct Login"];
            $sumAccesses += @$usageData[$year][$weekIndex]["Referring URL"];
            $sumAccesses += @$usageData[$year][$weekIndex]["IP Address"];
            
            $grandTotal += $sumAccesses;
            
            if( 0 == $sumAccesses )
            {
                echo ",\"\"";
            }
            else
            {
                echo "," . $sumAccesses;
            }
        }
    }
    echo "," . $grandTotal;
    echo "\n";

    unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
    unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
    unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );    
}
*/
?>