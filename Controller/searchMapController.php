<?php

namespace AbsoluteValue\MarkerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query\Expr\Join;

use AbsoluteValue\MarkerBundle\Helpers\MarkerHelper;
use AbsoluteValue\MarkerBundle\Helpers\ControllerHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;

use AbsoluteValue\PropertyBundle\Entity\Sale;
use AbsoluteValue\PropertyBundle\Entity\Property;
use AbsoluteValue\PropertyBundle\Entity\Listing;
use AbsoluteValue\PropertyBundle\Entity\Lease;
use AbsoluteValue\PropertyBundle\Entity\Category;
use AbsoluteValue\PropertyBundle\Form\Type\PropertyType;
use AbsoluteValue\PropertyBundle\Entity\SugarEntry;
use AbsoluteValue\MarkerBundle\Entity\mapSearch;



class searchMapController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
         $content = $this->renderView('MarkerBundle:marker:index.html.twig');
        return new Response($content);
    }
    
     /**
     * @Route("/form/set/element/property")
     * @Template()
     */
    public function setFormPropertyTypeAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb ->select("cat")
            ->from("PropertyBundle:Category", "cat")
            ->where("cat.fixed = true")
            ->orderBy("cat.id","ASC");
        $classfications= $qb->getQuery()->getArrayResult();
 
        $content = $this->renderView('MarkerBundle:marker:index.html.twig', array(
            'classfications' => $classfications,
        ));
        return new Response($content);
    }
    
     /**
     * @Route("/search/map/property")
     * @Template()
     */
    public function searchMapPropertyAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $helper = new MarkerHelper();
        $location = array();
 
        $location = $helper->MutltiData();
        $locationArray = $helper->getIncreaedLonLatAction($location);
        $data = json_encode($locationArray);
        
        return new Response($data);
    } 
    
    /**
     * @Route("/search/map/by/address")
     * @Template()
     */
    public function searchMapByAddressAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $property = new Property();
        $helper = new MarkerHelper();
        $location = array();
        $address = $request->get('value');
        //$address = "Mount Roskill";
  
        $byAdd = $em->createQueryBuilder();
        $byAdd ->select("p as property", "cat as category", "suEN as owner", "rtype.description as rtype_description")
                ->from("PropertyBundle:Property", "p")
                ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id")   
                ->innerJoin("p.relationships",'sug')
                ->innerJoin("sug.type",'rtype')
                ->innerJoin("sug.target",'suEN')
                ->orWhere("p.streetAddress1 like '%$address%'")
                ->orWhere("p.suburb like '%$address%'")
                ->andWhere("cat.fixed = true")
                ->andWhere("p.status = 'active'")
                ->orderBy("p.id","ASC");
        $propertyO = $byAdd->getQuery()->getScalarResult();

        if(isset($propertyO) && !empty($propertyO))
        {
            foreach ($propertyO as $propertyOS )
            {   
                $type = 'property';
                $location[$type][] = $helper->getLocationArrayData($propertyOS,$type);
            }

            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
        }else
        {
            $response = $helper->getGeoCodeAction($address);
            if(isset($response) && !empty($response))
            {
                $geometry = $response['results'][0]['geometry'];
                $latitude = strval($geometry['location']['lat']);
                $longitude = strval($geometry['location']['lng']);

                $byAdd = $em->createQueryBuilder();
                $byAdd ->select("p as property", "cat as category", "suEN as owner", "rtype.description as rtype_description")
                        ->from("PropertyBundle:Property", "p")
                        ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id")   
                        ->innerJoin("p.relationships",'sug')
                        ->innerJoin("sug.type",'rtype')
                        ->innerJoin("sug.target",'suEN')
                        ->where("p.latitude = $latitude")
                        ->andWhere("p.longitude = $longitude")
                        ->andWhere("cat.fixed = true")
                        ->andWhere("p.status = 'active'")
                        ->orderBy("p.id","ASC");
                $property = $byAdd->getQuery()->getScalarResult();

                if(isset($property) && !empty($property))
                {
                    foreach ($property as $propertys )
                    {   
                        $type = 'property';
                        $location[$type][] = $helper->getLocationArrayData($propertys,$type);
                    }

                    $locationArray = $helper->getIncreaedLonLatAction($location);
                    $data = json_encode($locationArray);
                }else{
                    $data = json_encode(array(['veraMsg' => 'The address you are searching is not listed in VERA properties']));
                }
            }else
            {
                $data = json_encode(array(['veraMsg' => 'The address you are searching is not listed in VERA properties']));
            }
        }

    return new Response($data);
    }
    
    /**
     * @Route("/search/map/by/client")
     * @Template()
     */
    public function searchMapByClientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unit = new Property();
        $helper = new MarkerHelper();
        $location = array();
        $clientname = $request->get('value');

        $byAdd = $em->createQueryBuilder();
        $byAdd ->select("p as property", "cat as category", "suEN as owner", "rtype.description as rtype_description" )
                ->from("PropertyBundle:Property", "p")
                ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id") 
                ->innerJoin("p.relationships",'sug')
                ->innerJoin("sug.type",'rtype')
                ->innerJoin("sug.target",'suEN')
                ->where("suEN.name like '%$clientname%'")
                ->orderBy("p.id","ASC");
        $property = $byAdd->getQuery()->getScalarResult();
        
        if(isset($property) && !empty($property))
        {
            foreach ($property as $propertys )
            {   
                $type = 'property';
                $location[$type][] = $helper->getLocationArrayData($propertys,$type);
            }

            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
        }else
        {
            $data = json_encode(array(['veraMsg' => 'Cannot able to find entered VERA Client' ]));
        }

        return new Response($data); 
    }
    
    /**
     * @Route("/search/map/by/tags")
     * @Template()
     */
    public function searchMapByTagsAction(Request $request)
    {   
        $helper = new MarkerHelper();
        $mapsearch = new mapSearch();
        $tagName = $request->get('value');
        $location = array();
        
        $em = $this->getDoctrine()->getManager();
        $tagRepository = $em->getRepository("TagBundle:Tag");

        $resourcePropertyIds = array();
        foreach(explode(",", $tagName) as $tag) {
            $resourcePropertyIds = array_merge($resourcePropertyIds, $tagRepository->getResourceIdsForTag("property", $tag));
        }
        if($resourcePropertyIds)
        {
            $qbp = $em->createQueryBuilder();
            $qbp = $mapsearch->getPropertyData($qbp);
            $qbp->andWhere($qbp->expr()->in("p.id", $resourcePropertyIds));
            $property = $qbp->getQuery()->getScalarResult();
            
            foreach ($property as $properties )
            {   
                $type = 'tags';
                $location[$type][] = $helper->getLocationArrayData($properties,$type);
            }
        }
        
        $resourceLeaseIds = array();
        foreach(explode(",", $tagName) as $tag) {
            $resourceLeaseIds = array_merge($resourceLeaseIds, $tagRepository->getResourceIdsForTag("lease", $tag));
        }
        if($resourceLeaseIds)
        {
            $qbs = $em->createQueryBuilder();
            $qbs = $mapsearch->getRentData($qbs);
            $qbs ->andWhere($qbs->expr()->in("le.id", $resourceLeaseIds));
            $rents = $qbs->getQuery()->getScalarResult();
            
            foreach ($rents as $keys => $rentOp)
            {    
                if(!isset($relation[$rentOp['le_id']]))
                {
                    $relation[$rentOp['le_id']] = array('lease'=>$rentOp);
                }
                $relation[$rentOp['le_id']]['lease']['relation'][$rentOp['sug_id']] = array('type'=>$rentOp['rtype_description'], 'name'=>$rentOp['suEN_name'] );
                $relation[$rentOp['le_id']]['lease']['storeinfo'][] = array('quantity'=>$rentOp['line_quantity'], 'unitOfMeasurement'=>$rentOp['line_unitOfMeasurement'],'netRate'=>$rentOp['line_netRate'] );
                 
            }
            
            foreach ($relation as $rentsec)
            {   
                foreach ($rentsec as $rents)
                {  
                    $type = 'rent';
                    $location[$type][] = $helper->getLocationArrayData($rents,$type);    
                }
            }
        }
        
        $resourceSaleIds = array();
        foreach(explode(",", $tagName) as $tag) {
            $resourceSaleIds = array_merge($resourceSaleIds, $tagRepository->getResourceIdsForTag("sale", $tag));
        }
        if($resourceSaleIds)
        {
            $qbs = $em->createQueryBuilder();
            $qbs = $mapsearch->getSaleData($qbs);
            $qbs ->andWhere($qbs->expr()->in("sale.id", $resourceSaleIds));
            $sales = $qbs->getQuery()->getScalarResult();
            
            foreach ($sales as $sale )
            {   
                $type = 'sale';
                $location[$type][] = $helper->getLocationArrayData($sale,$type);
            }
        }
        
        
        $locationArray = $helper->getIncreaedLonLatAction($location);
        if(isset($locationArray) && !empty($locationArray))
        {
            $data = json_encode($locationArray);
        }else
        {
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA properties, Try with another keyword' ]));
        }
        
        return new Response($data); 
    }
    
    
     /**
     * @Route("/search/map/by/rents", name="search_map_by_rent")
     * @Template()
     */
    public function searchMapByRentsAction(Request $request)
    {   
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $mapsearch = new mapSearch();
        $helper = new MarkerHelper();
        $location = array();
        $fields  = array();
        $relation = array();
        
        $fields['primaryClass'] = $request->get('primaryClass'); 
        $fields['primaryRegion'] = $request->get('primaryRegion');
        $fields['primaryType'] = $request->get('primaryType');
        $fields['tenure'] = $request->get('tenure');
        $fields['minDate'] = $helper->convertDate($request->get('minDate'));
        $fields['maxDate'] = $helper->convertDate($request->get('maxDate'));
        $fields['minContractRent'] = $request->get('minContractRent');
        $fields['maxContractRent'] = $request->get('maxContractRent');
        $fields['minQuantity'] = $request->get('minQuantity');
        $fields['maxQuantity'] = $request->get('maxQuantity');
        $fields['minNetRate'] = $request->get('minNetRate');
        $fields['maxNetRate'] = $request->get('maxNetRate');
        
        $qbr = $em->createQueryBuilder();
        $qbr = $mapsearch->getQueryForRent($qbr,$fields);
        $rent = $qbr->getQuery()->getScalarResult();

        if(isset($rent) && !empty($rent))
        {
            foreach ($rent as $keys => $rentOp)
            {    
                if(!isset($relation[$rentOp['le_id']]))
                {
                    $relation[$rentOp['le_id']] = array('lease'=>$rentOp);
                }
                $relation[$rentOp['le_id']]['lease']['relation'][$rentOp['sug_id']] = array('type'=>$rentOp['rtype_description'], 'name'=>$rentOp['suEN_name'] );
                $relation[$rentOp['le_id']]['lease']['storeinfo'][] = array('quantity'=>$rentOp['line_quantity'], 'unitOfMeasurement'=>$rentOp['line_unitOfMeasurement'],'netRate'=>$rentOp['line_netRate'] );
                $relation[$rentOp['le_id']]['lease']['reactType'][$rentOp['react_id']] = array('react_type'=>$rentOp['react_type'],'react_date'=>new \DateTime($rentOp['react_date'])); 
            }  
            //echo '<pre>'; var_dump($relation);echo '</pre>';exit();
            foreach ($relation as $rentsec)
            {   
                foreach ($rentsec as $rents)
                {  
                    $type = 'rent';
                    $location[$type][] = $helper->getLocationArrayData($rents,$type);    
                }
            }//echo '<pre>'; var_dump($location);echo '</pre>';exit();
            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
            
        }else{
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA rent properties' ]));
        }
        
        

        $time_end = microtime(true);
        
        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        return new Response($data); 
    }

     /**
     * @Route("/search/map/by/sales", name="search_map_by_sale")
     * @Template()
     */
    public function searchMapBySalesAction(Request $request)
    {   
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $mapsearch = new mapSearch();
        $helper = new MarkerHelper();
        $location = array();
        $fields  = array();
        $relation = array();

        $fields['primaryClass'] = $request->get('primaryClass'); 
        $fields['primaryRegion'] = $request->get('primaryRegion');
        $fields['primaryType'] = $request->get('primaryType');
        $fields['tenure'] = $request->get('tenure');
        $fields['minSaleDate'] = $helper->convertDate($request->get('minSaleDate'));
        $fields['maxSaleDate'] = $helper->convertDate($request->get('maxSaleDate'));
        $fields['minSalePrice'] = $request->get('minSalePrice');
        $fields['maxSalePrice'] = $request->get('maxSalePrice');
        $fields['minLettableArea'] = $request->get('minLettableArea');
        $fields['maxLettableArea'] = $request->get('maxLettableArea');
        $fields['minWalt'] = $request->get('minWalt');
        $fields['maxWalt'] = $request->get('maxWalt');

        $qbs = $em->createQueryBuilder();
        $qbs = $mapsearch->getQueryForSale($qbs,$fields);
        $sale = $qbs->getQuery()->getScalarResult();
        
        if(isset($sale) && !empty($sale))
        {
            foreach ($sale as $sales)
            {  
                $type = 'sale';
                $location[$type][] = $helper->getLocationArrayData($sales,$type);    
            }//echo '<pre>'; var_dump($location);echo '</pre>';exit();
        
            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
            
        }else{
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA sale properties' ]));
        }

        $time_end = microtime(true);
        
        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        return new Response($data); 
    }
    
    
    /**
     * @Route("/search/map/by/lists", name="search_map_by_lists")
     * @Template()
     */
    public function searchMapByListsAction(Request $request)
    {   
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $mapsearch = new mapSearch();
        $helper = new MarkerHelper();
        $location = array();
        $fields  = array();
        $relation = array();
 
        $fields['primaryClass'] = $request->get('primaryClass'); 
        $fields['primaryRegion'] = $request->get('primaryRegion');
        $fields['primaryType'] = $request->get('primaryType');
        $fields['tenure'] = $request->get('tenure');
        $fields['minListingDate'] = $helper->convertDate($request->get('minListingDate'));
        $fields['maxListingDate'] = $helper->convertDate($request->get('maxListingDate'));
        $fields['minCloseDate'] = $helper->convertDate($request->get('minCloseDate'));
        $fields['maxCloseDate'] = $helper->convertDate($request->get('maxCloseDate'));

        $qbs = $em->createQueryBuilder();
        $qbs = $mapsearch->getQueryForListings($qbs,$fields);
        $lists = $qbs->getQuery()->getScalarResult();
      
        if(isset($lists) && !empty($lists))
        {
            foreach ($lists as $listings)
            {  
                $type = 'listing';
                $location[$type][] = $helper->getLocationArrayData($listings,$type);    
            }
           
            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
            
        }else{
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA listing Properties' ]));
        }  //echo '<pre>'; var_dump($location);echo '</pre>';exit();
       
        $time_end = microtime(true);
        
        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        return new Response($data); 
    }
    
    /**
     * @Route("/search/map/by/opportunity/rental", name="search_map_by_opportunity_rental")
     * @Template()
     */
    public function searchMapByOppartunityRentalAction(Request $request)
    {   
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $mapsearch = new mapSearch();
        $helper = new MarkerHelper();
        $location = array();
        $fields  = array();
        $relation = array();
        
        $fields['primaryClass'] = $request->get('primaryClass'); 
        $fields['primaryRegion'] = $request->get('primaryRegion');
        $fields['primaryType'] = $request->get('primaryType');
        $fields['tenure'] = $request->get('tenure');
        $fields['custExist'] = $request->get('custExist');
        $fields['minDate'] = $helper->convertDate($request->get('minDate'));
        $fields['maxDate'] = $helper->convertDate($request->get('maxDate'));
        $fields['minResolvedDate'] = $helper->convertDate($request->get('minResolvedDate'));
        $fields['maxResolvedDate'] = $helper->convertDate($request->get('maxResolvedDate'));
        $fields['rentType'] = $request->get('rentType');
        
        $qbr = $em->createQueryBuilder();
        $qbr = $mapsearch->getQueryForOppartunityRental($qbr,$fields);
        $rent = $qbr->getQuery()->getScalarResult();
        
        if(isset($rent) && !empty($rent))
        {
            foreach ($rent as $keys => $rentOp)
            {    
                if(!isset($relation[$rentOp['react_id']][$rentOp['le_id']]))
                {
                    $relation[$rentOp['react_id']][$rentOp['le_id']]  = array('lease'=>$rentOp);
                }
                $relation[$rentOp['react_id']][$rentOp['le_id']] ['lease']['relation'][$rentOp['sug_id']] = array('type'=>$rentOp['rtype_description'], 'name'=>$rentOp['suEN_name'] );
            }
            
            foreach ($relation as $rentsecA)
            {
                foreach ($rentsecA as $rentsec)
                {   
                    foreach ($rentsec as $rents)
                    {  
                        $type = 'oppRental';
                        $location[$type][] = $helper->getLocationArrayData($rents,$type);    
                    }
                }
            }//echo '<pre>'; var_dump($relation);echo '</pre>';exit();
            
            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
            
        }else{
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA rental opportunities' ]));
        }

        $time_end = microtime(true);
        
        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        return new Response($data); 
    }
    
     /**
     * @Route("/search/map/by/opportunity/listing", name="search_map_by_opportunity_listing")
     * @Template()
     */
    public function searchMapByOppartunityListingAction(Request $request)
    {   
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $mapsearch = new mapSearch();
        $helper = new MarkerHelper();
        $location = array();
        $fields  = array();
        $relation = array();
 
        $fields['primaryClass'] = $request->get('primaryClass'); 
        $fields['primaryRegion'] = $request->get('primaryRegion');
        $fields['primaryType'] = $request->get('primaryType');
        $fields['tenure'] = $request->get('tenure');
        $fields['custExist'] = $request->get('custExist');
        $fields['minDate'] = $helper->convertDate($request->get('minDate'));
        $fields['maxDate'] = $helper->convertDate($request->get('maxDate'));
        $fields['minResolvedDate'] = $helper->convertDate($request->get('minResolvedDate'));
        $fields['maxResolvedDate'] = $helper->convertDate($request->get('maxResolvedDate'));
        $fields['rentType'] = $request->get('rentType');
        
        $qbr = $em->createQueryBuilder();
        $qbr = $mapsearch->getQueryForOpportunityListing($qbr,$fields);
        $lists = $qbr->getQuery()->getScalarResult();
        
        if(isset($lists) && !empty($lists))
        {
            foreach ($lists as $listings)
            {  
                $type = 'oppListings';
                $location[$type][] = $helper->getLocationArrayData($listings,$type);    
            }
           
            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
            
        }else{
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA listing opportunities' ]));
        }  //echo '<pre>'; var_dump($location);echo '</pre>';exit();
       
        $time_end = microtime(true);
        
        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        return new Response($data); 
    }
    
    /**
    * @Route("/search/map/by/opportunity/customs", name="search_map_by_opportunity_customs")
    * @Template()
    */
    public function searchMapByOpportunityCustomAction(Request $request)
    {   
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $mapsearch = new mapSearch();
        $helper = new MarkerHelper();
        $location = array();
        $fields  = array();
        $relation = array();
 
        $fields['primaryClass'] = $request->get('primaryClass'); 
        $fields['primaryRegion'] = $request->get('primaryRegion');
        $fields['primaryType'] = $request->get('primaryType');
        $fields['tenure'] = $request->get('tenure');
        $fields['custExist'] = $request->get('custExist');
        $fields['minDate'] = $helper->convertDate($request->get('minDate'));
        $fields['maxDate'] = $helper->convertDate($request->get('maxDate'));
        $fields['minResolvedDate'] = $helper->convertDate($request->get('minResolvedDate'));
        $fields['maxResolvedDate'] = $helper->convertDate($request->get('maxResolvedDate'));
        $fields['rentType'] = $request->get('rentType');
        
        $qbr = $em->createQueryBuilder();
        $qbr = $mapsearch->getQueryForOpportunityCustoms($qbr,$fields);
        $lists = $qbr->getQuery()->getScalarResult();
      
        if(isset($lists) && !empty($lists))
        {
            foreach ($lists as $listings)
            {  
                $type = 'oppCustom';
                $location[$type][] = $helper->getLocationArrayData($listings,$type);    
            }
           
            $locationArray = $helper->getIncreaedLonLatAction($location);
            $data = json_encode($locationArray);
            
        }else{
            $data = json_encode(array(['veraMsg' => 'Cannot able to find VERA custom opportunities' ]));
        }  //echo '<pre>'; var_dump($location);echo '</pre>';exit();
       
        $time_end = microtime(true);
        
        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        return new Response($data); 
    }
    
    /**
    * @Route("/ajax/bucket/get/data" , name="marker_ajax_bucket_get_data")
    * @Template()
    */
    public function setAjaxBucketGetDataAction(Request $request)
    {
        $time_start = microtime(true); 
        
        $em = $this->getDoctrine()->getManager();
        $controllerHelper = new ControllerHelper();
        $markerHelper =  new MarkerHelper();
        $markerPinData = array();
        $markerPinDatatype;
 
        $bucketid = $request->get('bucketid');
        //$bucketid = 123;
        
        /*
        * Get Property Details by Bucket ID
        */
        $propertyBucketData = $controllerHelper->getPropertyBucketAction($em, $bucketid);
        if(isset($propertyBucketData) && !empty($propertyBucketData))
        {
            $markerPinDatatype[] = $propertyBucketData;
        }
        /*
        * Get Listing Details by Bucket ID
        */
        $listingBucketData= $controllerHelper->getListBucketAction($em, $bucketid);
        if(isset($listingBucketData) && !empty($listingBucketData))
        {
            $markerPinDatatype[] = $listingBucketData;
        }
        /*
        * Get Rent Details by Bucket ID
        */
        $rentBucketData= $controllerHelper->getRentBucketAction($em, $bucketid);
        if(isset($rentBucketData) && !empty($rentBucketData))
        {
            $markerPinDatatype[] = $rentBucketData;
        }
        /*
        * Get Sale Details by Bucket ID
        */
        $saleBucketData= $controllerHelper->getSaleBucketAction($em, $bucketid);
        if(isset($saleBucketData) && !empty($saleBucketData))
        {
            $markerPinDatatype[] = $saleBucketData;
        }

        if(isset($markerPinDatatype) && !empty($markerPinDatatype)){
            
            foreach($markerPinDatatype as $markerPinDatatypeA)
            {
                foreach($markerPinDatatypeA as $key => $markerPinDatatypeB)
                {
                    $markerPinData[$key] = $markerPinDatatypeB;
                }
            }
            
            $locationArray = $markerHelper->getIncreaedLonLatAction($markerPinData);
            
            $data = json_encode($locationArray);
            
            $time_end = microtime(true);
        
            //dividing with 60 will give the execution time in minutes other wise seconds
            $execution_time = ($time_end - $time_start)/60;
        
            return new Response($data);
            
        }else
        {
            return new Response();
        }
    }
    
}
