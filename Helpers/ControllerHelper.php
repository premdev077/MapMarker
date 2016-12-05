<?php

namespace AbsoluteValue\MarkerBundle\Helpers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query\Expr\Join;

use AbsoluteValue\MarkerBundle\Entity\mapSearch;

/**
 * Description of ControllerHelper.php
 *
 * @author premraj.tharmarajah
 */
class ControllerHelper {
    /**
    * Description
    * Check for Bucket Availibilty in Listing bucket Table
    * @author Premraj
    */
    public function listBucketAction($em,$bucket, $dataA, $buckettype)
    {
        $bylistbucket = $em->createQueryBuilder();
        $bucketId =  $bucket->getId();
        $result = array();
        
        $bylistbucket ->select("bucket")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.listing",'list')
                ->where("bucket.id = $bucketId")
                ->andWhere("list.id = $dataA")
                ->orderBy("bucket.id","ASC");
         
        $listbucket = $bylistbucket->getQuery()->getScalarResult(); 
 
        if(isset($listbucket) && !empty($listbucket))
        {
            $result['content'] = json_encode(array(
                  'message' => 'Property information already stored in this bucket'
            ));
            $result['status'] = 200;
            
            return $result;
        }
        else {
            $listing = $em->getRepository("PropertyBundle:Listing")->findOneById($dataA);

            if($bucket && $listing) {
                $em->persist($bucket);
                $bucket->addListing($listing);
                $em->flush();

                $result['content'] = json_encode(array(
                    'message' => 'Listing '.$listing->getId().' added to '.$bucket->getName().' bucket',
                    'color' => $bucket->getColor(),
                    'id' => $bucket->getId(),
                    'type' => $buckettype,
                    'obj_id' => $dataA
                ));
                
                $result['status'] = 200;
            
                return $result;
            }
            else {
                
                
                $result['content'] = json_encode(array(
                    'message' => 'Listing data or bucket data not available'
                ));
                
                $result['status'] = 400;
            
                return $result;
                
            }
        }
    }
    
    /**
    * Description
    * Check for Bucket Availibilty in Sale bucket Table
    * @author Premraj
    */
    public function saleBucketAction($em,$bucket, $dataA, $buckettype)
    {
        
        $bylistbucket = $em->createQueryBuilder();
        $bucketId =  $bucket->getId();
        $result = array();
        
        $bylistbucket ->select("bucket")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.sale",'sale')
                ->where("bucket.id = $bucketId")
                ->andWhere("sale.id = $dataA")
                ->orderBy("bucket.id","ASC");
         
        $salebucket = $bylistbucket->getQuery()->getScalarResult(); 
 
        if(isset($salebucket) && !empty($salebucket))
        {
            $result['content'] = json_encode(array(
                  'message' => 'Property information already stored in this bucket'
            ));
            
            $result['status'] = 200;
            
            return $result;
        }
        else {
            $sale = $em->getRepository('PropertyBundle:Sale')->findOneById($dataA);

            if ($bucket && $sale) {
                $em->persist($bucket);
                $bucket->addSale($sale);
                $em->flush();

                $result['content'] = json_encode(array(
                    'message' => "Sale ".$sale->getId()." added to ".$bucket->getName().' bucket',
                    'color' => $bucket->getColor(),
                    'id' => $bucket->getId(),
                    'type' => $buckettype,
                    'obj_id' => $dataA
                ));
                
                $result['status'] = 200;

                return $result;
            }
            else {

                $result['content'] = json_encode(array(
                    'message' => 'Sale data or bucket data not available'
                ));

                $result['status'] = 400;

                return $result;
            }
        }
    }
    
    /**
    * Description
    * Check for Bucket Availibilty in shedule bucket Table
    * @author Premraj
    */
    public function sheduleBucketAction($em,$bucket, $dataA, $buckettype)
    {
        
        $bylistbucket = $em->createQueryBuilder();
        $bucketId =  $bucket->getId();
        $result = array();
        
        $bylistbucket ->select("bucket")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.schedule",'sch')
                ->where("bucket.id = $bucketId")
                ->andWhere("sch.id = $dataA")
                ->orderBy("bucket.id","ASC");
         
        $rentbucket = $bylistbucket->getQuery()->getScalarResult(); 
 
        if(isset($rentbucket) && !empty($rentbucket))
        {
            $result['content'] = json_encode(array(
                  'message' => 'Property information already stored in this bucket'
            ));
            
            $result['status'] = 200;
            
            return $result;
        }
        else {
           $schedule = $em->getRepository("PropertyBundle:Schedule")->findOneById($dataA);

            if ($bucket && $schedule) {
                $em->persist($bucket);
                $bucket->addSchedule($schedule);
                $em->flush();

                $result['content'] = json_encode(array(
                    'message' => 'Schedule '.$schedule->getId().' added to '.$bucket->getName().' bucket',
                    'color' => $bucket->getColor(),
                    'id' => $bucket->getId(),
                    'type' => $buckettype,
                    'obj_id' => $dataA
                ));
                
                $result['status'] = 200;
            
                return $result;
            } 
            else {
                $result['content'] = json_encode(array(
                    'message' => 'Rent data or bucket data not available'
                ));
                
                $result['status'] = 400;
            
                return $result;
            }
        }
    }
    
    /**
    * Description
    * Check for Bucket Availibilty in property bucket Table
    * @author Premraj
    */
    public function propertyBucketAction($em,$bucket, $dataA, $buckettype)
    {
        
        $bylistbucket = $em->createQueryBuilder();
        $bucketId =  $bucket->getId();
        $result = array();
        
        $bylistbucket ->select("bucket")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.property",'pro')
                ->where("bucket.id = $bucketId")
                ->andWhere("pro.id = $dataA")
                ->orderBy("bucket.id","ASC");
         
        $propertybucket = $bylistbucket->getQuery()->getScalarResult(); 
 
        if(isset($propertybucket) && !empty($propertybucket))
        {
            $result['content'] = json_encode(array(
                  'message' => 'Property information already stored in this bucket'
            ));
            
            $result['status'] = 200;
            
            return $result;
        }
        else {
            $property = $em->getRepository('PropertyBundle:Property')->findOneById($dataA);
           
            if ($bucket && $property) {
                $em->persist($bucket);
                $bucket->addProperty($property);
                $em->flush();

                $result['content'] = json_encode(array(
                    'message' => $property->getName()." added to ".$bucket->getName().' bucket',
                    'color' => $bucket->getColor(),
                    'id' => $bucket->getId(),
                    'type' => $buckettype,
                    'obj_id' => $dataA
                ));
                
                $result['status'] = 200;
            
                return $result;
            
            } 
            else {

                $result['content'] = json_encode(array(
                    'message' => 'Property data or bucket data not available'
                ));
                
                $result['status'] = 400;
            
                return $result;
            }
        }
    }
    
    
    /**
    * Description
    * Check for Bucket Availibilty in property bucket Table
    * @author Premraj
    */
    public function getPropertyBucketAction($em,$bucketid)
    {   
        $markerHelper =  new MarkerHelper();
        $propertyData = array();
        $propertyList = array();
        $markerPinDataView = array();
        
        $byPropertyBucket = $em->createQueryBuilder();
        $byPropertyBucket ->select("bucket", "pro")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.property",'pro')
                ->where("bucket.id = $bucketid")
                ->orderBy("bucket.id","ASC");
         
        $propertyBucketData = $byPropertyBucket->getQuery()->getScalarResult(); 
        
        foreach($propertyBucketData as $propertyBucketDataA)
        {
            $byAdd = $em->createQueryBuilder();
            $byAdd ->select("p as property", "cat as category", "suEN as owner", "rtype.description as rtype_description")
                    ->from("PropertyBundle:Property", "p")
                    ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id")   
                    ->innerJoin("p.relationships",'sug')
                    ->innerJoin("sug.type",'rtype')
                    ->innerJoin("sug.target",'suEN')
                    ->where("p.id = :primaryid ")
                    ->setParameter("primaryid", $propertyBucketDataA["pro_id"])
                    ->orderBy("p.id","ASC");
            $propertyData[] = $byAdd->getQuery()->getScalarResult();
        }
        
        if(isset($propertyData) && !empty($propertyData))
        {
            foreach($propertyData as $propertyDataA)
            {
                foreach($propertyDataA as $propertyDataB)
                {
                    $type = 'property';
                    $markerPinData[$type][] = $markerHelper->getLocationArrayData($propertyDataB,$type);
                }
            }
            return $markerPinData;
        }
        else
        {
            return;
        }
    }
    
    /**
    * Description
    * Check for Bucket Availibilty in List bucket Table
    * @author Premraj
    */
    public function getListBucketAction($em,$bucketid)
    {   
        $markerHelper =  new MarkerHelper();
        $mapSearchEntity = new mapSearch();
        $listData= array();
        $markerPinData = array();
        
        $bylistBucket = $em->createQueryBuilder();
        $bylistBucket ->select("bucket", "list")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.listing","list")
                ->where("bucket.id = $bucketid")
                ->orderBy("bucket.id","ASC");
         
        $listBucketData = $bylistBucket->getQuery()->getScalarResult(); 
        
        foreach($listBucketData as $listBucketDataA)
        {
            $byAdd = $em->createQueryBuilder();
            $byAdd = $mapSearchEntity->getListData($byAdd);
            $byAdd ->andwhere("list.id = :listid ")
                   ->setParameter("listid", $listBucketDataA["list_id"])
                   ->orderBy("por.id","ASC");

            $listData[] = $byAdd->getQuery()->getScalarResult();
        }

        if(isset($listData) && !empty($listData))
        {
            foreach($listData as $listDataA)
            {
                foreach($listDataA as $listDataB)
                {
                    $type = 'listing';
                    $markerPinData[$type][] = $markerHelper->getLocationArrayData($listDataB,$type);
                }
            }
            return $markerPinData;
        }
        else
        {
            return;
        }
    }
    
    /**
    * Description
    * Check for Bucket Availibilty in List bucket Table
    * @author Premraj
    */
    public function getSaleBucketAction($em,$bucketid)
    {   
        $markerHelper =  new MarkerHelper();
        $mapSearchEntity = new mapSearch();
        $saleData= array();
        $markerPinData = array();
        
        $bySaleBucket = $em->createQueryBuilder();
        $bySaleBucket ->select("bucket", "sale")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.sale","sale")
                ->where("bucket.id = $bucketid")
                ->orderBy("bucket.id","ASC");
        $saleBucketData = $bySaleBucket->getQuery()->getScalarResult(); 
        
        foreach($saleBucketData as $saleBucketDataA)
        {
            $byAdd = $em->createQueryBuilder();
            $byAdd = $mapSearchEntity->getSaleData($byAdd);
            $byAdd ->andwhere("sale.id = :saleid ")
                   ->setParameter("saleid", $saleBucketDataA["sale_id"])
                   ->orderBy("por.id","ASC");

            $saleData[] = $byAdd->getQuery()->getScalarResult();
        }

        if(isset($saleData) && !empty($saleData))
        {
            foreach($saleData as $saleDataA)
            {
                foreach($saleDataA as $saleDataB)
                {
                    $type = 'sale';
                    $markerPinData[$type][] = $markerHelper->getLocationArrayData($saleDataB,$type);
                }
            }
            return $markerPinData;
        }
        else
        {
            return;
        }
    }
    
    /**
    * Description
    * Check for Bucket Availibilty in Rent bucket Table
    * @author Premraj
    */
    public function getRentBucketAction($em,$bucketid)
    {   
        $markerHelper =  new MarkerHelper();
        $mapSearchEntity = new mapSearch();
        $rentData= array();
        $markerPinData = array();
        
        $byRentBucket = $em->createQueryBuilder();
        $byRentBucket ->select("bucket", "sch")
                ->from("PropertyBundle:Bucket", "bucket")
                ->innerJoin("bucket.schedule","sch")
                ->where("bucket.id = $bucketid")
                ->orderBy("bucket.id","ASC");
         
        $rentBucketData = $byRentBucket->getQuery()->getScalarResult(); 

        foreach($rentBucketData as $rentBucketDataA)
        {
            $byAdd = $em->createQueryBuilder();
            $byAdd = $mapSearchEntity->getRentData($byAdd);
            $byAdd ->andwhere("sch.id = :schid ")
                   ->setParameter("schid", $rentBucketDataA["sch_id"])
                   ->orderBy("por.id","ASC");

            $rentData[] = $byAdd->getQuery()->getScalarResult();
        }

        if(isset($rentData) && !empty($rentData))
        {   
            foreach ($rentData as $rentDataA)
            {    
                foreach ($rentDataA as $keys => $rentOp)
                { 
                    if(!isset($relation[$rentOp['le_id']]))
                    {
                        $relation[$rentOp['le_id']] = array('lease'=>$rentOp);
                    }
                    $relation[$rentOp['le_id']]['lease']['relation'][$rentOp['sug_id']] = array('type'=>$rentOp['rtype_description'], 'name'=>$rentOp['suEN_name'] );
                    $relation[$rentOp['le_id']]['lease']['storeinfo'][] = array('quantity'=>$rentOp['line_quantity'], 'unitOfMeasurement'=>$rentOp['line_unitOfMeasurement'],'netRate'=>$rentOp['line_netRate'] );
                    $relation[$rentOp['le_id']]['lease']['reactType'][$rentOp['react_type']] = array('react_type'=>$rentOp['react_type'], 'react_date'=>new \DateTime($rentOp['react_date'])); 
                    }   
            }
            
            foreach($relation as $rentDataAA)
            {
                foreach($rentDataAA as $rentDataBB)
                {
                    $type = 'rent';
                    $markerPinData[$type][] = $markerHelper->getLocationArrayData($rentDataBB,$type);
                }
            }
            return $markerPinData;
        }
        else
        {
            return;
        }
    }
}