<?php
namespace AbsoluteValue\MarkerBundle\Helpers;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarkerHelper
 *
 * @author Premraj
 */
class MarkerHelper {
    
    /**
    * Description
    * Find Out Address Longtitute Latite using google geocode api
    * @author Premraj
    */
    public function getGeoCodeAction($location)
    {
        $api = 'XXXXX';
        $address = str_replace(" ", "+", urlencode($location));
        
        $details_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=".$api;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);
        $info = curl_getinfo($ch);
        //$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlresult=curl_exec($ch);

        if(preg_match("/OK/i", $curlresult))
        {
            return $response;
        }
        else
        {
            return;
        }

        //Close handle
        curl_close($ch);
        
    }
    
    public function getLocationXmlAction($location)
    {
        // Start XML file, create parent node
        $doc =  new \DomDocument( "1.0", "ISO-8859-15" );
        $node = $doc->createElement("markers");
        $parnode = $doc->appendChild($node);
        // Set the content type to be XML, so that the browser will   recognise it as XML.
        header( "content-type: application/xml; charset=ISO-8859-15" );
        // Iterate through the rows, adding XML nodes for each
        foreach ($location as $row ){
            // ADD TO XML DOCUMENT NODE
            $nodes = $doc->createElement("marker");
            $newnode = $parnode->appendChild($nodes);
            $newnode->setAttribute("name", $row['name']);
            $newnode->setAttribute("address", $row['address']);
            $newnode->setAttribute("lat", $row['lat']);
            $newnode->setAttribute("lng", $row['lng']);
            $newnode->setAttribute("type", $row['type']);
        }
        $xmlfile = $doc->saveXML();
        
        return $xmlfile;
    }
    
    public function getIncreaedLonLatAction($locationArray)
    {
        $location =array();
        $dups = array();
   
        foreach($locationArray as $keyAr => $locationArrays )
        {
            foreach($locationArrays as $keyLo => $locationA )
            {
                $dups[$keyAr][$locationA['id']][] = $locationA;
            }
        }
          
        foreach($dups as $dupkeys => $dupsArray )
        {
            foreach($dupsArray as $keyDup => $locationB )
            {
                $location[$keyDup][] = $locationB;
               
            } 
        }
        
        $arrayset = $this->sendIncreaedLonLatAction($location);
        
        return $arrayset;
        
    }
    public function convertDate($param) {
        if($param)
        {
            $newDate = date("Y-m-d", strtotime($param));
        }else
        {
            $newDate="";
        }
        
        return $newDate;
    }
    
    public function dateDifference($date1, $date2)
    {
        $diff1 = date_diff($date1, $date2);
        $getDays = $diff1->format('%d');
        
        $dayDifferent = 31 - $getDays;
        
        $dateStringLast = $date1->format('d-m-Y');
        $dateIncreaseLast = strtotime ( '+'.$dayDifferent.' days' , strtotime ( $dateStringLast ) ) ;
        $newDateLast = date('d-m-Y' , $dateIncreaseLast );
       
        $dateConvertDate = new \DateTime($newDateLast);
        
        $newDateDiff = date_diff($date2, $dateConvertDate );
        if( $newDateDiff->format('%m') == 0)
        {
            $datePassData = $newDateDiff->format('%y Years');
        }else
        {
            $datePassData = $newDateDiff->format('%y Years %m Months');
        }
        
        return $datePassData;
    }
    
    public function sendIncreaedLonLatAction($location)
    {
        $arrayLocations = array();
        $zeroValue =  array();
        $longArray = array();
        $arrayset = array();
        $i = 0;
        $n = 0;
        
        foreach($location as  $count )
        {
           if(count($count) > 1)
            {   
                $number = count($count)-1;
                for($e=0; $number > $e; $e++ )
                {   
                    $firstLat = $count[$e][0]['lat'];
                    $firstLng = $count[$e][0]['lng'];
                    $increase = 0.000200;

                    if(strpos($firstLat, '-') !== false) {
                        $latInc = strval($firstLat - $increase);
                    }else {
                        $latInc = strval($firstLat + $increase);
                    }

                    if(strpos($firstLng, '-') !== false) {
                        $lngInc = strval($firstLng - $increase);
                    }else {
                        $lngInc = strval($firstLng + $increase);
                    }

                    $count[$e+1][0]['lat'] = $latInc;
                    $count[$e+1][0]['lng'] = $lngInc;
                }

                foreach($count as $counts)
                {
                    $arrayLocations[] = $counts;
                }

                $i++;
            }
            else {
                foreach($count as $counts)
                {
                    $arrayLocations[] = $counts;
                }
            }        
        }

        foreach($arrayLocations as $arrayLocationA)
        {   
            foreach($arrayLocationA as $arrayLocationB)
            {
                $longArray[$arrayLocationB['lat']][] = $arrayLocationB;
            }
        }
        
        foreach($longArray as $longArrayA)
        {   
            $arrayset[] = $longArrayA;
        }
        
        return $arrayset;
    }
    
    public function getLocationArrayData($data, $type)
    {
        if($type == 'property')
        {
            $location = array(
                'id' => $data['p_id'],
                'relation' => $data['p_id'],
                'address' => $data['p_streetAddress1'].' '.$data['p_suburb'].' '.$data['p_city'].' '.$data['p_postCode'].' New Zealand',
                'lat' =>  $data['p_latitude'],
                'lng' => $data['p_longitude'],
                'html' => $this->propertyHtml($data),
                'type' => $data['cat_description'],
                'IconType' => $data['cat_description'],
            );
        }
        else if($type == 'rent')
        {
            $location = array(
                'id' => $data['por_id'],
                'relation' => $data['sch_id'],
                'address' => $data['por_streetAddress1'].' '.$data['por_suburb'].' '.$data['por_city'].' '.$data['por_postCode'].' New Zealand',
                'lat' =>  $data['por_latitude'],
                'lng' => $data['por_longitude'],
                'html' => $this->rentHtml($data),
                'type' => 'Rents',  
                'IconType' => 'rents',
            );  
        }
        else if($type == 'sale')
        {
           $location = array(
                'id' => $data['por_id'],
                'relation' => $data['sale_id'],
                'address' => $data['por_streetAddress1'].' '.$data['por_suburb'].' '.$data['por_city'].' '.$data['por_postCode'].' New Zealand',
                'lat' =>  $data['por_latitude'],
                'lng' => $data['por_longitude'],
                'html' => $this->salesHtml($data),
                'type' => 'Sales',
                'IconType' => 'sales',
            );  
        }
        else if($type == 'listing')
        {
           $location = array(
                'id' => $data['por_id'],
                'relation' => $data['list_id'],
                'address' => $data['por_streetAddress1'].' '.$data['por_suburb'].' '.$data['por_city'].' '.$data['por_postCode'].' New Zealand',
                'lat' =>  $data['por_latitude'],
                'lng' => $data['por_longitude'],
                'html' => $this->listingHtml($data),
                'type' => 'Listings',
                'IconType' => 'listing',
            );  
        }
        else if($type == 'tags')
        {
            $location = array(
                'id' => $data['p_id'],
                'relation' => $data['p_id'],
                'address' => $data['p_streetAddress1'].' '.$data['p_suburb'].' '.$data['p_city'].' '.$data['p_postCode'].' New Zealand',
                'lat' =>  $data['p_latitude'],
                'lng' => $data['p_longitude'],
                'html' => $this->propertyHtml($data),
                'type' => $data['cat_description'],
                'IconType' => 'property',
            );
        }
        else if($type == 'oppRental')
        {
           $location = array(
                'id' => $data['por_id'],
                'relation' => $data['le_id'],
                'address' => $data['por_streetAddress1'].' '.$data['por_suburb'].' '.$data['por_city'].' '.$data['por_postCode'].' New Zealand',
                'lat' =>  $data['por_latitude'],
                'lng' => $data['por_longitude'],
                'html' => $this->OppartunityRentHtml($data),
                'type' => 'Rental',
                'IconType' => 'waiting',
            );  
        }
        else if($type == 'oppListings')
        {
           $location = array(
                'id' => $data['por_id'],
                'relation' => $data['list_id'],
                'address' => $data['por_streetAddress1'].' '.$data['por_suburb'].' '.$data['por_city'].' '.$data['por_postCode'].' New Zealand',
                'lat' =>  $data['por_latitude'],
                'lng' => $data['por_longitude'],
                'html' => $this->OppartunityListingHtml($data),
                'type' => 'Listings',
                'IconType' => 'urgent',
            );  
        }
        else if($type == 'oppCustom')
        {
           $location = array(
                'id' => $data['p_id'],
                'relation' => $data['act_id'],
                'address' => $data['p_streetAddress1'].' '.$data['p_suburb'].' '.$data['p_city'].' '.$data['p_postCode'].' New Zealand',
                'lat' =>  $data['p_latitude'],
                'lng' => $data['p_longitude'],
                'html' => $this->OpportunityCustomHtml($data),
                'type' => 'Custom',
                'IconType' => 'done',
            );  
        }
       
        return $location;
    }
    
    public function listingHtml($data)
    {
        
        $html ='<a class="link_to_property_page" href="/property/'.$data['por_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['por_streetAddress1'].', '.$data['por_suburb'].', '.$data['por_city'].', '.$data['por_postCode'].', New Zealand.</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-cloud"></i> Method Of Sale</div>
                    <div class="Pr_Ad_value">'.$data['list_method'].'</div>
                </div>';
                if(!empty($data['list_listingDate']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Listing Date </div>
                                <div class="Pr_Ad_value">'.$data['list_listingDate']->format('d-m-Y').'</div>
                            </div>';
                }
                if(!empty($data['list_closeDate']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Close Date </div>
                                <div class="Pr_Ad_value">'.$data['list_closeDate']->format('d-m-Y').'</div>
                            </div>';
                }
                if(!empty($data['rtype_description']) && !empty($data['suEN_name']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-user-secret"></i> CRM Relationship </div>
                                <div class="Pr_Ad_value">'.$data['rtype_description'].'</div>
                            </div><div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-user"></i> Owner Name</div>
                                <div class="Pr_Ad_value">'.$data['suEN_name'].'</div>
                            </div>';
                }
    
        $html .= '<div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-info"></i> Sale Info </div>
                    <div class="Pr_Ad_value">';
                       if($data['list_sold']){ 'SOLD';}else{'NOT SOLD';}
        $html .= '  </div>
                  </div>';
        
        $html .= '<div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-info"></i> Resolve Info </div>
                    <div class="Pr_Ad_value">';
                       if($data['list_resolved']){ 'Resolved';}else{'NOT Resolved';}
        $html .= '  </div>
                  </div>';
                
        $html .= '<div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-paragraph"></i> Resolve Note </div>
                    <div class="Pr_Ad_value">'.$data['list_resolvedNotes'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-steam"></i> Pipeline Stage </div>
                    <div class="Pr_Ad_value">'.$data['list_stage'].'</div>
                </div>';
        
        $html .= '<div class="ajax-bucket-msg ajax-list-msg-'.$data['list_id'].' margin-left-25 bg-success padding-5 hide"><i class="fa fa-check"></i> Added to Bucket</div>
                <a href="javascript:void(0)" onclick="addBucketInfoAction('.$data['por_id'].','.$data['list_id'].')" class="btn btn-info margin-top-10 margin-bottom-10 margin-left-25">
                    <i class="fa fa-plus"></i> 
                        Add to Bucket
                </a>';
        
        return $html;
    }

    
    public function salesHtml($data)
    {
        $html ='<a class="link_to_property_page" href="/property/'.$data['por_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['por_streetAddress1'].', '.$data['por_suburb'].', '.$data['por_city'].', '.$data['por_postCode'].', New Zealand.</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-dollar"></i> Sale Price</div>
                    <div class="Pr_Ad_value">'.number_format($data['sale_salePrice']).'</div>
                </div>';
                if(!empty($data['sale_saleDate']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Sale Date </div>
                                <div class="Pr_Ad_value">'.$data['sale_saleDate']->format('d-m-Y').'</div>
                            </div>';
                }
                if(!empty($data['rtype_description']) && !empty($data['suEN_name']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-user-secret"></i> CRM Relationship </div>
                                <div class="Pr_Ad_value">'.$data['rtype_description'].'</div>
                            </div><div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-user"></i> Owner Name</div>
                                <div class="Pr_Ad_value">'.$data['suEN_name'].'</div>
                            </div>';
                }
        $html .= '<div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-soundcloud"></i> Source </div>
                    <div class="Pr_Ad_value">'.$data['sale_source'].'</div>
                </div>
                <div class=" Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-krw"></i> Walt </div>
                    <div class="Pr_Ad_value">'.$data['sale_walt'].'</div>
                </div>
                <div class="Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-percent"></i> Occupied </div>
                    <div class="Pr_Ad_value">'.$data['sale_occupied'].' %</div>
                </div>
                <div class=" Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-area-chart"></i> Lettable Area </div>
                    <div class="Pr_Ad_value">'.$data['sale_lettableArea'].' Sqm</div>
                </div>
                <div class=" Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-car"></i> Car Parks </div>
                    <div class="Pr_Ad_value"># '.$data['sale_carParks'].'</div>
                </div>
                <div class="Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-percent"></i> Initial Yield </div>
                    <div class="Pr_Ad_value">'.$data['sale_initialYield'].' %</div>
                </div>
                <div class="Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-percent"></i> Market Yield </div>
                    <div class="Pr_Ad_value">'.$data['sale_marketYield'].' %</div>
                </div>
                <div class="Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-percent"></i> Equivalent Yield </div>
                    <div class="Pr_Ad_value">'.$data['sale_initialYield'].' %</div>
                </div>
                <div class="Pr_Ad_info_2">
                    <div class="Pr_Ad_label"><i class="fa fa-percent"></i> IRR </div>
                    <div class="Pr_Ad_value">'.$data['sale_irr'].' %</div>
                </div>
                
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-pencil"></i> Comments </div>
                    <div class="Pr_Ad_value">'.$data['sale_comments'].'</div>
                </div>';

        $html .= '<div class="ajax-bucket-msg ajax-sale-msg-'.$data['sale_id'].' margin-left-25 bg-success padding-5 hide"><i class="fa fa-check"></i> Added to Bucket</div>
                <a href="javascript:void(0)" onclick="addBucketInfoAction('.$data['por_id'].','.$data['sale_id'].')" class="btn btn-info margin-top-10 margin-bottom-10 margin-left-25">
                    <i class="fa fa-plus"></i> 
                    Add to Bucket
                </a>';

        return $html;
    }

    public function rentHtml($data)
    {
        $html ='<a class="link_to_property_page" href="/property/'.$data['por_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['por_streetAddress1'].', '.$data['por_suburb'].', '.$data['por_city'].', '.$data['por_postCode'].', New Zealand.</div>
                </div>';
        if(!empty($data['le_expirationDate']) && !empty($data['le_commencementDate']))
        {   
            $date = $this->dateDifference($data['le_expirationDate'], $data['le_commencementDate']);
            $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Commencement | Expiration Date </div>
                        <div class="Pr_Ad_value">'.$data['le_commencementDate']->format('d-m-Y').' | '. $data['le_expirationDate']->format('d-m-Y') .'</div>
                    </div>';
            $html .= '<div class=" Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-area-chart"></i> Lease Term</div>
                        <div class="Pr_Ad_value">'.$date.'</div>
                    </div>';
        }elseif(!empty($data['le_commencementDate']))
        {
            $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> commencement </div>
                        <div class="Pr_Ad_value">'.$data['le_commencementDate']->format('d-m-Y').'</div>
                    </div>';
        }
        foreach($data['relation'] as $keys => $relations)
        {
            $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-user"></i> '.$relations['type'].' </div>
                        <div class="Pr_Ad_value">'.$relations['name'].'</div>
                    </div>';
        }
        $html .= '<div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-dollar"></i> Contract Rent </div>
                    <div class="Pr_Ad_value">'.number_format($data['sch_contractRent']).'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-pencil"></i> Rent Review </div>
                    <div class="Pr_Ad_value">'.$data['le_rentReview'].'</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-check"></i> Right Of Renewal </div>
                    <div class="Pr_Ad_value">'.$data['le_rightOfRenewal'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-sitemap"></i> Rent Type</div>
                    <div class="Pr_Ad_value">'.$data['le_type'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-sitemap"></i> Rental Activity Type</div>';
                            $i = 0;
                            foreach($data['reactType'] as $keys => $reactType)
                            {                               
                                $html .= '<div class="Pr_Ad_value"><i class="fa fa-circle-o-notch"></i> '.$reactType['react_type'].' : '.$reactType['react_date']->format('d-m-Y').'</div>';
                                $i++;
                                
                            }
        $html .= '</div>
                <div class="ajax-bucket-msg ajax-rent-msg-'.$data['sch_id'].' margin-left-25 bg-success padding-5 hide"><i class="fa fa-check"></i> Added to Bucket</div>
                <a href="javascript:void(0)" onclick="addBucketInfoAction('.$data['por_id'].','.$data['sch_id'].')" class="btn btn-info margin-top-10 margin-bottom-10 margin-left-25">
                    <i class="fa fa-plus"></i> 
                    Add to Bucket
                </a>';

        return $html;
    }
    
    public function propertyHtml($data)
    {
        $html ='<a class="link_to_property_page" href="/property/'.$data['p_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-user"></i> Property Owner</div>
                    <div class="Pr_Ad_value">' .$data['suEN_name'];
                    if(isset($data['suEN_phoneNumber']) && !empty($data['suEN_phoneNumber']))
                    {
                        $html.= ' | '.$data['suEN_phoneNumber'];
                    }        
        $html.=     '</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-tags"></i> CRM Relationship </div>
                    <div class="Pr_Ad_value">'.$data['rtype_description'].'</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['p_streetAddress1'].', '.$data['p_suburb'].', '.$data['p_city'].', '.$data['p_postCode'].', New Zealand.</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-cog"></i> Year Built</div>
                    <div class="Pr_Ad_value">'.$data['p_year_built'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-area-chart"></i> Land Area</div>
                    <div class="Pr_Ad_value">'.$data['p_land_area'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-sort-numeric-asc"></i> Title Number</div>
                    <div class="Pr_Ad_value">'.$data['p_titleNumber'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-compass"></i> Tenure</div>
                    <div class="Pr_Ad_value">'.$data['p_tenure'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-paragraph"></i> Description</div>
                    <div class="Pr_Ad_value text-justify">'.$data['p_description'].'</div>
                </div>';
        
        $html .= '<div class="ajax-bucket-msg ajax-property-msg-'.$data['p_id'].' margin-left-25 bg-success padding-5 hide"><i class="fa fa-check"></i> Added to Bucket</div>
                <a href="javascript:void(0)" onclick="addBucketInfoAction('.$data['p_id'].','.$data['p_id'].')" class="btn btn-info margin-top-10 margin-bottom-10 margin-left-25">
                    <i class="fa fa-plus"></i> 
                    Add to Bucket
                </a>';
        
        return $html;
    }
    
    
    public function OppartunityRentHtml($data)
    {
        $html ='<a class="link_to_property_page" href="/property/'.$data['por_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['por_streetAddress1'].', '.$data['por_suburb'].', '.$data['por_city'].', '.$data['por_postCode'].', New Zealand.</div>
                </div>';
        if(!empty($data['le_expirationDate']) && !empty($data['le_commencementDate']))
        {
            $interval = date_diff($data['le_expirationDate'], $data['le_commencementDate']);
            $date = $interval->format('%y Years %m Months'); 
                
            $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Commencement | Expiration Date </div>
                        <div class="Pr_Ad_value">'.$data['le_commencementDate']->format('d-m-Y').' | '. $data['le_expirationDate']->format('d-m-Y') .'</div>
                    </div>';
            $html .= '<div class=" Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-area-chart"></i> Lease Term</div>
                        <div class="Pr_Ad_value">'.$date.'</div>
                    </div>';
        }elseif(!empty($data['le_commencementDate']))
        {
            $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> commencement </div>
                        <div class="Pr_Ad_value">'.$data['le_commencementDate']->format('d-m-Y').'</div>
                    </div>';
        }
        $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Activty Date </div>
                        <div class="Pr_Ad_value">'.$data['act_date']->format('d-m-Y').'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-sitemap"></i> Rent Type</div>
                    <div class="Pr_Ad_value">'.$data['le_type'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-pencil"></i> Rent Review </div>
                    <div class="Pr_Ad_value">'.$data['le_rentReview'].'</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-check"></i> Right Of Renewal </div>
                    <div class="Pr_Ad_value">'.$data['le_rightOfRenewal'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-sitemap"></i> Rental Type</div>
                    <div class="Pr_Ad_value">'.$data['react_type'].'</div>
                </div>';
        
        foreach($data['relation'] as $keys => $relations)
        {
            $html .= '<div class="Pr_Ad_info">
                        <div class="Pr_Ad_label"><i class="fa fa-user"></i> '.$relations['type'].' </div>
                        <div class="Pr_Ad_value">'.$relations['name'].'</div>
                    </div>';
        }
        
        return $html;
    }
    
    public function OppartunityListingHtml($data)
    {
        $html ='<a class="link_to_property_page" href="/property/'.$data['por_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['por_streetAddress1'].', '.$data['por_suburb'].', '.$data['por_city'].', '.$data['por_postCode'].', New Zealand.</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-cloud"></i> Method Of Sale</div>
                    <div class="Pr_Ad_value">'.$data['list_method'].'</div>
                </div>';
                if(!empty($data['list_listingDate']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Listing Date </div>
                                <div class="Pr_Ad_value">'.$data['list_listingDate']->format('d-m-Y').'</div>
                            </div>';
                }
                if(!empty($data['list_closeDate']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-calendar"></i> Close Date </div>
                                <div class="Pr_Ad_value">'.$data['list_closeDate']->format('d-m-Y').'</div>
                            </div>';
                }
                if(!empty($data['rtype_description']) && !empty($data['suEN_name']))
                {
                    $html .= '<div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-user-secret"></i> CRM Relationship </div>
                                <div class="Pr_Ad_value">'.$data['rtype_description'].'</div>
                            </div><div class="Pr_Ad_info">
                                <div class="Pr_Ad_label"><i class="fa fa-user"></i> Owner Name</div>
                                <div class="Pr_Ad_value">'.$data['suEN_name'].'</div>
                            </div>';
                }
    
        $html .= '<div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-info"></i> Sale Info </div>
                    <div class="Pr_Ad_value">';
                       if($data['list_sold']){ 'SOLD';}else{'NOT SOLD';}
        $html .= '  </div>
                  </div>';
        
        $html .= '<div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-info"></i> Resolve Info </div>
                    <div class="Pr_Ad_value">';
                       if($data['list_resolved']){ 'Resolved';}else{'NOT Resolved';}
        $html .= '  </div>
                  </div>';
                
        $html .= '<div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-paragraph"></i> Resolve Note </div>
                    <div class="Pr_Ad_value">'.$data['list_resolvedNotes'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-steam"></i> Pipeline Stage </div>
                    <div class="Pr_Ad_value">'.$data['list_stage'].'</div>
                </div>';
        
        return $html;
    }
    
     public function OpportunityCustomHtml($data)
    {
        $html ='<a class="link_to_property_page" href="/property/'.$data['p_slug'].'" target="_blank"> Find More >> </a>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-user"></i> Property Owner</div>
                    <div class="Pr_Ad_value">' .$data['suEN_name'].' - '.$data['suEN_phoneNumber'].'</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-map-marker"></i> Address</div>
                    <div class="Pr_Ad_value">'.$data['p_streetAddress1'].', '.$data['p_suburb'].', '.$data['p_city'].', '.$data['p_postCode'].', New Zealand.</div>
                </div>
                <div class="Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-cog"></i> Year Built</div>
                    <div class="Pr_Ad_value">'.$data['p_year_built'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-area-chart"></i> Land Area</div>
                    <div class="Pr_Ad_value">'.$data['p_land_area'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-sort-numeric-asc"></i> Title Number</div>
                    <div class="Pr_Ad_value">'.$data['p_titleNumber'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-compass"></i> Tenure</div>
                    <div class="Pr_Ad_value">'.$data['p_tenure'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-tags"></i> Custom Type </div>
                    <div class="Pr_Ad_value">'.$data['custAct_description'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-pencil"></i> Resolved Notes</div>
                    <div class="Pr_Ad_value">'.$data['act_resolvedNotes'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-pencil"></i> Activity Notes</div>
                    <div class="Pr_Ad_value">'.$data['act_activityNotes'].'</div>
                </div>
                <div class=" Pr_Ad_info">
                    <div class="Pr_Ad_label"><i class="fa fa-paragraph"></i> Description</div>
                    <div class="Pr_Ad_value">'.$data['p_description'].'</div>
                </div>';
        
        return $html;
    }
    
    public function MutltiData()
    {
        $location =  array();
        
        $location['property'] = array(
                0 => 
                    array (
                        'id' => '101',
                        'name' => 'Pan Africa Market',
                        'address' => '1521 1st Ave, Seattle, WA',
                        'lat' =>  '54.828526',
                        'lng' => '-7.467593',
                        'type' => 'property'
                    ),
                1 => 
                  array (
                        'id' => '302',
                        'name' => 'The Melting Pot',
                        'address' => '14 Mercer St, Seattle, WA',
                        'lat' => '-36.847813',
                        'lng' => '174.763016',
                        'type' => 'property'
                    ),
                2 => 
                  array (
                        'id' => '302',
                        'name' => 'The Melting Pot',
                        'address' => '14 Mercer St, Seattle, WA',
                        'lat' => '-36.847813',
                        'lng' => '174.763016',
                        'type' => 'property'
                    ),
                3 => 
                  array (
                        'id' => '222',
                        'name' => 'Ipanema Grill',
                        'address' => '1225 1st Ave, Seattle, WA',
                        'lat' =>'-36.846699',
                        'lng' => '174.775223',
                        'type' => 'property'
                    ),
                4 => 
                  array (
                        'id' => '542',
                        'name' => 'Sake House',
                        'address' => '2230 1st Ave, Seattle, WA',
                        'lat' =>  '-36.846840',
                        'lng' =>  '174.763367',
                        'type' =>  'property'
                    ),
                5 => 
                  array (
                        'id' => '642',
                        'name' => 'Crab Pot',
                        'address' => '1301 Alaskan Way, Seattle, WA',
                        'lat' => '-36.856133',
                        'lng' => '174.742752',
                        'type' => 'property', 
                      ),
                6 => 
                  array (
                        'id' => '642',
                        'name' => 'Crab Pot Test 1',
                        'address' => '1301 Alaskan Way, Seattle, WA',
                        'lat' => '-36.856133',
                        'lng' => '174.742752',
                        'type' => 'property', 
                      ),
                7 => 
                  array (
                        'id' => '642',
                        'name' => 'Crab Pot Test 2',
                        'address' => '1301 Alaskan Way, Seattle, WA',
                        'lat' => '-36.856133',
                        'lng' => '174.742752',
                        'type' => 'property', 
                      ),

        );
        $location['rents'] = array(
                0 => 
                  array (
                        'id' => '744',
                        'name' => 'Wingdome',
                        'address' => '1416 E Olive Way, Seattle, WA',
                        'lat' => '-36.848885',
                        'lng' => '174.783966',
                        'type' => 'rents'
                      ),
                1 => 
                  array (
                        'id' => '302',
                        'name' => 'Piroshky Piroshky',
                        'address' => '1908 Pike pl, Seattle, WA',
                        'lat' =>  '-36.850536',
                        'lng' => '174.778442',
                        'type' => 'rents',
                    ),
                2 => 
                  array (
                        'id' => '222',
                        'name' => 'test 2',
                        'address' => '2222 2nd Ave, Seattle, WA',
                        'lat' => '-36.743294',
                        'lng' => '174.717804',
                        'type' => 'rents'
                    ),
                3 => 
                  array (
                        'id' => '222',
                        'name' => 'test 3',
                        'address' => '2222 2nd Ave, Seattle, WA',
                        'lat' => '-36.743294',
                        'lng' => '174.717804',
                        'type' => 'rents'
                    ),
                4 => 
                  array (
                        'id' => '222',
                        'name' => 'Buddha Thai & Bar',
                        'address' => '2222 2nd Ave, Seattle, WA',
                        'lat' => '-36.743294',
                        'lng' => '174.717804',
                        'type' => 'rents'
                    ),
        );                   
        return $location;
    }
    
    public function SampleLonLat()
    {
        $location = array(
                0 => 
                    array (
                        'id' => '1',
                        'name' => 'Pan Africa Market',
                        'address' => '1521 1st Ave, Seattle, WA',
                        'lat' =>  '54.828526',
                        'lng' => '-7.467593',
                        'type' => 'rents'
                    ),
                1 => 
                  array (
                        'id' => '3',
                        'name' => 'The Melting Pot',
                        'address' => '14 Mercer St, Seattle, WA',
                        'lat' => '-36.847813',
                        'lng' => '174.763016',
                        'type' => 'done'
                    ),
                2 => 
                  array (
                        'id' => '3',
                        'name' => 'The Melting Pot',
                        'address' => '14 Mercer St, Seattle, WA',
                        'lat' => '-36.847813',
                        'lng' => '174.763016',
                        'type' => 'rents'
                    ),
                3 => 
                  array (
                        'id' => '4',
                        'name' => 'Ipanema Grill',
                        'address' => '1225 1st Ave, Seattle, WA',
                        'lat' =>'-36.846699',
                        'lng' => '174.775223',
                        'type' => 'rents'
                    ),
                4 => 
                  array (
                        'id' => '5',
                        'name' => 'Sake House',
                        'address' => '2230 1st Ave, Seattle, WA',
                        'lat' =>  '-36.846840',
                        'lng' =>  '174.763367',
                        'type' =>  'rents'
                    ),
                5 => 
                  array (
                        'id' => '6',
                        'name' => 'Crab Pot',
                        'address' => '1301 Alaskan Way, Seattle, WA',
                        'lat' => '-36.856133',
                        'lng' => '174.742752',
                        'type' => 'rents', 
                      ),
                6 => 
                  array (
                        'id' => '7',
                        'name' => 'Wingdome',
                        'address' => '1416 E Olive Way, Seattle, WA',
                        'lat' => '-36.848885',
                        'lng' => '174.783966',
                        'type' => 'rents'
                      ),
                7 => 
                  array (
                        'id' => '8',
                        'name' => 'Piroshky Piroshky',
                        'address' => '1908 Pike pl, Seattle, WA',
                        'lat' =>  '-36.850536',
                        'lng' => '174.778442',
                        'type' => 'rents',
                    ),
                8 => 
                  array (
                        'id' => '2',
                        'name' => 'test 2',
                        'address' => '2222 2nd Ave, Seattle, WA',
                        'lat' => '-36.743294',
                        'lng' => '174.717804',
                        'type' => 'property'
                    ),
                9 => 
                  array (
                        'id' => '2',
                        'name' => 'test 3',
                        'address' => '2222 2nd Ave, Seattle, WA',
                        'lat' => '-36.743294',
                        'lng' => '174.717804',
                        'type' => 'sales'
                    ),
                10 => 
                  array (
                        'id' => '2',
                        'name' => 'Buddha Thai & Bar',
                        'address' => '2222 2nd Ave, Seattle, WA',
                        'lat' => '-36.743294',
                        'lng' => '174.717804',
                        'type' => 'rents'
                    ),
        );
        
        return $location;
    }
}
