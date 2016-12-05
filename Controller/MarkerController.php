<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
use AbsoluteValue\PropertyBundle\Entity\SugarRelationship;
use AbsoluteValue\PropertyBundle\Entity\SugarEntry;

/**
 * Description of MakerController
 * @author Premraj
 */
class MarkerController extends Controller {
    /**
     * @Route("/" , name="marker_index")
     * @Template()
     */
    public function indexAction()
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
     * @Route("/listing" , name="marker_listing")
     * @Template()
     */
    public function listingAction()
    {
        $content = $this->renderView('MarkerBundle:marker:listing.html.twig');
        return new Response($content);
    }
    
    
    /**
     * @Route("/sale" , name="marker_sale")
     * @Template()
     */
    public function saleAction()
    {
        $content = $this->renderView('MarkerBundle:marker:sale.html.twig');
        return new Response($content);
    }
    
    
    /**
     * @Route("/lease" , name="marker_lease")
     * @Template()
     */
    public function leaseAction()
    {
        $content = $this->renderView('MarkerBundle:marker:lease.html.twig');
        return new Response($content);
    }

    /**
    * @Route("/xml/full" , name="marker_xml_all_data")
    * @Template()
    */
    public function getMapXmlFullDataAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $helper = new MarkerHelper();
        $location = array();

        $byAdd = $em->createQueryBuilder();

        $byAdd ->select("p as property", "cat as category","suEN as owner","sug","rtype.description as rtype_description" )
                ->from("PropertyBundle:Property", "p")
                ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id")   
                ->innerJoin("p.relationships",'sug')
                ->innerJoin("sug.type",'rtype')
                ->innerJoin("sug.target",'suEN')
                ->where("p.latitude != 0")
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
            $data = json_encode(array(['veraMsg' => 'The address your searching is not listed in vera Property']));
        }
        return new Response($data);
    }
    
    /**
    * @Route("/ajax/all/property" , name="marker_ajax_all_property_data")
    * @Template()
    */
    public function getMapAjaxAllPropertyDataAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entity = new Property();
        $helper = new MarkerHelper();
        $location = array();

        $byAdd = $em->createQueryBuilder();

        $byAdd ->select("p as property", "cat as category","suEN as owner","sug","rtype.description as rtype_description" )
                ->from("PropertyBundle:Property", "p")
                ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id")   
                ->innerJoin("p.relationships",'sug')
                ->innerJoin("sug.type",'rtype')
                ->innerJoin("sug.target",'suEN')
                ->where("p.latitude != 0")
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
            $data = json_encode(array(['veraMsg' => 'The address your searching is not listed in vera Property']));
        }
        return new Response($data);
    }
    
    /**
    * @Route("/ajax/bucket/store" , name="marker_ajax_store_data")
    * @Template()
    */
    public function setAjaxBucketDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $control = new ControllerHelper();
        $dataProperty = array();
        
        $bucketid = $request->get('bucketid'); 
        $buckettype = $request->get('type');
        $dataA = $request->get('data');
        
        /*
         * Created Foreach for group duplicate data into on group using key..
         * example: $jsondata {'0':{'id':815,'nid':30},'1':{'id':815,'nid':30}} duplicates mutltiple times..
         * group this data into single group ex : {'id':815,'nid':30}
         */
        foreach($dataA as $key => $dataAA)
        {
            $dataProperty[$dataAA["id"]][$dataAA["nid"]] = array('id'=> $dataAA["id"], 'nid'=> $dataAA["nid"] );
        }
        if(isset($dataProperty) && !empty($dataProperty))
        {
            foreach($dataProperty as $dataPropertyA)
            {
                foreach($dataPropertyA as $dataPropertyB)
                {
                    $em = $this->getDoctrine()->getManager();
                    $bucket = $em->getRepository("PropertyBundle:Bucket")->findOneById($bucketid);
                    
                    if($buckettype == 'listing')
                    {
                        $result = $control->listBucketAction($em, $bucket, $dataPropertyB['nid'], $buckettype ); 
                    }
                    elseif($buckettype == 'sales')
                    {
                        $result = $control->saleBucketAction($em, $bucket, $dataPropertyB['nid'], $buckettype );
                    }
                    elseif($buckettype == 'rent')
                    {
                        $result = $control->sheduleBucketAction($em, $bucket, $dataPropertyB['nid'], $buckettype );
                    }
                    elseif($buckettype == 'property')
                    {
                        $result = $control->propertyBucketAction($em, $bucket, $dataPropertyB['nid'], $buckettype );
                    }
                    else
                    {
                        $result['content'] = json_encode(array(
                            'message' => 'Bucket type is not Available.. please try to search again'
                        ));
                        $result['status'] = 400;
                    }
                }     
            }
            return new Response($result['content'], $result['status'], array('Content-Type' => 'application/json'));
        }else
        {
            $content = json_encode(array(
                'message' => 'Sorry no property selected to add'
            ));

            return new Response($content, 400, array('Content-Type' => 'application/json'));
        }
    }

    /**
    * @Route("/ajax/bucket/info/store" , name="marker_ajax_info_store_data")
    * @Template()
    */
    public function setAjaxInfoBucketDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $control = new ControllerHelper();
        
        $bucketid = $request->get('bucketid'); 
        $buckettype = $request->get('type');
        $dataA = $request->get('data');
        $dataPropertyB= $request->get('property');
        
        if(isset($dataA) && !empty($dataA))
        {
            $em = $this->getDoctrine()->getManager();
            $bucket = $em->getRepository("PropertyBundle:Bucket")->findOneById($bucketid);

            if($buckettype == 'listing')
            {
                $result = $control->listBucketAction($em, $bucket, $dataA, $buckettype );
            }
            elseif($buckettype == 'sales')
            {
                $result = $control->saleBucketAction($em, $bucket, $dataA, $buckettype );
            }
            elseif($buckettype == 'rent')
            {
                $result = $control->sheduleBucketAction($em, $bucket, $dataA, $buckettype );
            }
            elseif($buckettype == 'property')
            {
                $result = $control->propertyBucketAction($em, $bucket, $dataA, $buckettype );
            }
            else
            {
                $result['content'] = json_encode(array(
                    'message' => 'Bucket type is not Available.. please try to search again'
                ));
                $result['status'] = 400;
            }
            
            return new Response($result['content'], $result['status'], array('Content-Type' => 'application/json'));
           
        }else
        {
            $content = json_encode(array(
                'message' => 'Sorry no property selected to add'
            ));

            return new Response($content, 400, array('Content-Type' => 'application/json'));
        }
    }
        
     /**
     * @Route("/curl/geocode/api/request" , name="ajax_geocode_api_request")
     * @Template()
     */
    public function ajaxGeoCodeApiAction()
    {   
       
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb ->select("s")
            ->add("from", "PropertyBundle:Property s")
            ->where("s.latitude is NULL")
            ->andWhere("s.longitude is NULL")
            ->orderBy("s.id","ASC");
        $choice = $qb->getQuery()->getArrayResult();
        
        $data = array();
        $i = 0;
        
        foreach($choice as $choices)
        {
            $data['property']['address'] = $choices['streetAddress1'].' '.$choices['suburb'].' '.$choices['city'].' '.$choices['postCode'].' New Zealand';
            $data['property']['id'] = $choices['id'];
           
            $helper = new MarkerHelper();
            $response = $helper->getGeoCodeAction($data['property']['address']);
            
            // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
            if ($response['status'] == 'OK') {   
                
                $geometry = $response['results'][0]['geometry'];
                
                $longitude = $geometry['location']['lng'];
                $latitude = $geometry['location']['lat'];
                
                $entity = new Property();
                $entity = $em->getRepository('PropertyBundle:Property')->find($data['property']['id']);
                
                if($entity){
                    $entity->setLatitude($latitude);
                    $entity->setLongitude($longitude);
                    $em->flush();
                }
            }else{   
                
                $longitude = 0;
                $latitude = 0;
                
                $entity = new Property();
                $entity = $em->getRepository('PropertyBundle:Property')->find($data['property']['id']);
                
                if($entity){
                    $entity->setLatitude($latitude);
                    $entity->setLongitude($longitude);
                    $em->flush();
                }
            } 
             
            $i++;
        }

        $city = "28 Lorne Street Auckland City Auckland  New Zealand";
        
        $helper = new MarkerHelper();
        $response = $helper->getGeoCodeAction($city);
            
        $geometry = $response['results'][0]['geometry'];
                
         $array = array(
             'latitude' => $geometry['location']['lat'],
             'longitude' => $geometry['location']['lng'],
             'location_type' => $geometry['location_type'],
         );

         
        $content = $this->renderView('MarkerBundle:marker:curl.html.twig',
            array(
                // last username entered by the user
                'location' =>$array,
            )
        );
        
   
        
        return new Response($content);
    }

    /**
     * @Route("/curl/geocode/api/request/zero" , name="ajax_geocode_api_request_zero")
     * @Template()
     */
    public function ajaxGeoCodeApiZeroAction()
    {   
       
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb ->select("s")
            ->add("from", "PropertyBundle:Property s")
            ->where("s.latitude = 0")
            ->orderBy("s.id","ASC");
        $choice = $qb->getQuery()->getArrayResult();
        
        $data = array();
        $i = 0;
        
        foreach($choice as $choices)
        {
            $data['property']['address'] = $choices['streetAddress1'].' '.$choices['suburb'].' '.$choices['city'].' '.$choices['postCode'].' New Zealand';
            $data['property']['id'] = $choices['id'];
           
            $helper = new MarkerHelper();
            $response = $helper->getGeoCodeAction($data['property']['address']);
            
            // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
            if ($response['status'] == 'OK') {   
                
                $geometry = $response['results'][0]['geometry'];
                
                $longitude = $geometry['location']['lng'];
                $latitude = $geometry['location']['lat'];
                
                $entity = new Property();
                $entity = $em->getRepository('PropertyBundle:Property')->find($data['property']['id']);
                
                if($entity){
                    $entity->setLatitude($latitude);
                    $entity->setLongitude($longitude);
                    $em->flush();
                }
                //var_dump($response);
                //exit();
            }else{   
                
                $longitude = 0;
                $latitude = 0;
                
                $entity = new Property();
                $entity = $em->getRepository('PropertyBundle:Property')->find($data['property']['id']);
                
                if($entity){
                    $entity->setLatitude($latitude);
                    $entity->setLongitude($longitude);
                    $em->flush();
                }
            } 
             
            $i++;
        }

        $city = "28 Lorne Street Auckland City Auckland  New Zealand";
        
        $helper = new MarkerHelper();
        $response = $helper->getGeoCodeAction($city);
            
        $geometry = $response['results'][0]['geometry'];
                
         $array = array(
             'latitude' => $geometry['location']['lat'],
             'longitude' => $geometry['location']['lng'],
             'location_type' => $geometry['location_type'],
         );

         
        $content = $this->renderView('MarkerBundle:marker:curl.html.twig',
            array(
                // last username entered by the user
                'location' =>$array,
            )
        );
        
   
        
        return new Response($content);
    }
}