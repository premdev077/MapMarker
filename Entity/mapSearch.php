<?php

namespace AbsoluteValue\MarkerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr\Join;
use DoctrineExtensions\Taggable\Taggable;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\ExecutionContextInterface;

use AbsoluteValue\PropertyBundle\Entity\Sale;
use AbsoluteValue\PropertyBundle\Entity\Property;
use AbsoluteValue\PropertyBundle\Entity\Listing;
use AbsoluteValue\PropertyBundle\Entity\Lease;
/**
 * @ORM\Entity
 * @ORM\Table(name="map_search")
 * @ORM\HasLifecycleCallbacks()
 */
class mapSearch {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AbsoluteValue\UserBundle\Entity\User")
     */
    protected $user;
    
    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    protected $type;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $data;
    
    /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     * 
     * @ORM\Column(type="datetime")
     */
    protected $updated;


    function getType() {
        return $this->type;
    }

    function getCreated() {
        return $this->created;
    }

    function getUpdated() {
        return $this->updated;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function setUpdated($updated) {
        $this->updated = $updated;
    }
   
    public function getId() {
        return $this->id;
    }

    public function getUser() {
        return $this->user;
    }

    public function getData() {
        return $this->data;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setData($data) {
        $this->data = $data;
    }
    
    /**
    * @ORM\PrePersist
    * @ORM\PreUpdate
    */
    public function updatedTimestamps()
    {
        $this->setUpdated(new \DateTime(date('Y-m-d H:i:s')));

        if($this->getCreated() == null)
        {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }
    
    public function getPropertyData($qbp) {
        
        $qbp ->select("p as property", "cat as category","suEN as owner","sug","rtype.description as rtype_description")
                    ->from("PropertyBundle:Property", "p")
                    ->innerJoin("PropertyBundle:Category", "cat", Join::WITH, "p.primaryClassification = cat.id")   
                    ->innerJoin("p.relationships",'sug')
                    ->innerJoin("sug.type",'rtype')
                    ->innerJoin("sug.target",'suEN');
        
        return $qbp;
    
    }
    
    public function getRentData($qbr)
    {
        $qbr ->select(
                "por as property",
                "le as Lease",
                "sug.id as sug_id",
                "rtype.description as rtype_description",
                "sch.contractRent as sch_contractRent",
                "sch.id as sch_id",
                "line.netRate as line_netRate",
                "line.unitOfMeasurement as line_unitOfMeasurement",
                "line.quantity as line_quantity", 
                "react.type as react_type",
                "react.date as react_date",
                "react.id as react_id",
                "suEN.id as suEN_id",
                "suEN.name as suEN_name"
                )    
            ->from("PropertyBundle:Property", "por")
            ->innerJoin("PropertyBundle:Lease", "le", Join::WITH, "por.id = le.property")
            ->innerJoin("le.relationships",'sug')
            ->innerJoin("sug.type",'rtype')
            ->innerJoin("sug.target",'suEN') 
            ->innerJoin("PropertyBundle:RentalActivity", "react", Join::WITH, "le.id = react.lease")
            ->leftJoin("PropertyBundle:Schedule", "sch", Join::WITH, "react.id = sch.rentalActivity")
            ->leftJoin("PropertyBundle:LineItem", "line", Join::WITH, "sch.id = line.schedule")
            ->where("por.latitude != 0")
            ->andWhere("por.longitude != 0")
            ->andWhere("por.status = 'active'");
        
        return $qbr;
    }
    
    public function getQueryForRent($qbr, $fields) {

            $qbr = $this->getRentData($qbr);
            
            if($fields['primaryClass'] != 0 && !empty($fields['primaryClass']))
            {
                $qbr->andWhere("por.primaryClassification = :primaryClass ")
                ->setParameter("primaryClass", $fields['primaryClass']);
            }
            if($fields['primaryRegion'] != 0 && !empty($fields['primaryRegion']))
            {
                $qbr->andWhere("por.primaryRegion = :primaryRegion")
                ->setParameter("primaryRegion", $fields['primaryRegion']);
            }
            if($fields['primaryType'] != 0 && !empty($fields['primaryType']))
            {
                $qbr->andWhere("por.primaryType = :primaryType")
                ->setParameter("primaryType", $fields['primaryType']);
            }
            if($fields['tenure'] != '0' && !empty($fields['tenure']))
            {
                $qbr->andWhere("por.tenure = :tenure")
                ->setParameter("tenure", $fields['tenure']);
            }
            if(!empty($fields['minDate'] ))
            {
                $qbr->andWhere("react.date >= :minDate")
                ->setParameter("minDate", $fields['minDate']);
            }
            if(!empty($fields['maxDate'] ))
            {
                $qbr->andWhere("react.date <= :maxDate")
                ->setParameter("maxDate", $fields['maxDate']);
            }
            if(!empty($fields['minContractRent']))
            {
                $qbr->andWhere("sch.contractRent >= :minContractRent")
                ->setParameter("minContractRent", $fields['minContractRent']);
            }
            if(!empty($fields['maxContractRent']))
            {
                $qbr->andWhere("sch.contractRent <= :maxContractRent")
                ->setParameter("maxContractRent", $fields['maxContractRent']);
            }
            if(!empty($fields['minQuantity']))
            {
                $qbr->andWhere("line.quantity >= :minQuantity")
                ->setParameter("minQuantity", $fields['minQuantity']);
            }
            if(!empty($fields['maxQuantity']))
            {
                $qbr->andWhere("line.quantity <= :maxQuantity")
                 ->setParameter("maxQuantity", $fields['maxQuantity']);
            }
            if(!empty($fields['minNetRate']))
            {
                $qbr->andWhere("line.netRate >= :minNetRate")
                ->setParameter("minNetRate", $fields['minNetRate']);
            }
            if(!empty($fields['maxNetRate']))
            {
                $qbr->andWhere("line.netRate <= :maxNetRate")
                ->setParameter("maxNetRate", $fields['maxNetRate']);
            }
            //$qbr->groupBy("sug.id");
            //$qbr->setMaxResults('500');
            $qbr->orderBy("le.id","ASC");
            
            return $qbr;
    }
    
    public function getSaleData($qbs)
    {
        $qbs ->select(
                    "por as property",
                    "sale as sales",
                    "sug.id as sug_id",
                    "rtype.description as rtype_description",
                    "suEN.id as suEN_id",
                    "suEN.name as suEN_name"
                )    
            ->from("PropertyBundle:Property", "por")
            ->innerJoin("PropertyBundle:Sale", "sale", Join::WITH, "por.id = sale.property")
            ->innerJoin("por.relationships",'sug')
            ->innerJoin("sug.type",'rtype')
            ->innerJoin("sug.target",'suEN') 
            ->where("por.latitude != 0")
            ->andWhere("por.longitude != 0")
            ->andWhere("por.status = 'active'");
        
        return $qbs;
    }
     public function getQueryForSale($qbs, $fields) {
        
            $qbs = $this->getSaleData($qbs);
        
            if($fields['primaryClass'] != 0 && !empty($fields['primaryClass']))
            {
                $qbs->andWhere("por.primaryClassification = :primaryClass ")
                ->setParameter("primaryClass", $fields['primaryClass']);
            }
            if($fields['primaryRegion'] != 0 && !empty($fields['primaryRegion']))
            {
                $qbs->andWhere("por.primaryRegion = :primaryRegion")
                ->setParameter("primaryRegion", $fields['primaryRegion']);
            }
            if($fields['primaryType'] != 0 && !empty($fields['primaryType']))
            {
                $qbs->andWhere("por.primaryType = :primaryType")
                ->setParameter("primaryType", $fields['primaryType']);
            }
            if($fields['tenure'] != '0' && !empty($fields['tenure']))
            {
                $qbs->andWhere("por.tenure = :tenure")
                ->setParameter("tenure", $fields['tenure']);
            }
            if(!empty($fields['minSaleDate'] ))
            {
                $qbs->andWhere("sale.saleDate >= :minSaleDate")
                ->setParameter("minSaleDate", $fields['minSaleDate']);
            }
            if(!empty($fields['maxSaleDate'] ))
            {
                $qbs->andWhere("sale.saleDate <= :maxSaleDate")
                ->setParameter("maxSaleDate", $fields['maxSaleDate']);
            }
            if(!empty($fields['minSalePrice']))
            {
                $qbs->andWhere("sale.salePrice >= :minSalePrice")
                ->setParameter("minSalePrice", $fields['minSalePrice']);
            }
            if(!empty($fields['maxSalePrice']))
            {
                $qbs->andWhere("sale.salePrice <= :maxSalePrice")
                ->setParameter("maxSalePrice", $fields['maxSalePrice']);
            }
            if(!empty($fields['minLettableArea']))
            {
                $qbs->andWhere("sale.lettableArea >= :minLettableArea")
                ->setParameter("minLettableArea", $fields['minLettableArea']);
            }
            if(!empty($fields['maxLettableArea']))
            {
                $qbs->andWhere("sale.lettableArea <= :maxLettableArea")
                 ->setParameter("maxLettableArea", $fields['maxLettableArea']);
            }
            if(!empty($fields['minWalt']))
            {
                $qbs->andWhere("sale.walt >= :minWalt")
                ->setParameter("minWalt", $fields['minWalt']);
            }
            if(!empty($fields['maxWalt']))
            {
                $qbs->andWhere("sale.walt <= :maxWalt")
                ->setParameter("maxWalt", $fields['maxWalt']);
            }
            $qbs->orderBy("por.id","ASC");
            
            
            return $qbs;
    }
    
    public function getListData($qbs)
    {
        $qbs ->select(
                    "por as property",
                    "list as listings",
                    "sug.id as sug_id",
                    "rtype.description as rtype_description",
                    "suEN.id as suEN_id",
                    "suEN.name as suEN_name"
                )    
            ->from("PropertyBundle:Property", "por")
            ->innerJoin("PropertyBundle:Listing", "list", Join::WITH, "por.id = list.property")
            ->innerJoin("list.relationships",'sug')
            ->innerJoin("sug.type",'rtype')
            ->innerJoin("sug.target",'suEN')
            ->where("por.latitude != 0")
            ->andWhere("por.longitude != 0");
        
        return $qbs;
    }

    public function getQueryForListings($qbs, $fields) {
        
            $qbs = $this->getListData($qbs);
        
            if($fields['primaryClass'] != 0 && !empty($fields['primaryClass']))
            {
                $qbs->andWhere("por.primaryClassification = :primaryClass ")
                ->setParameter("primaryClass", $fields['primaryClass']);
            }
            if($fields['primaryRegion'] != 0 && !empty($fields['primaryRegion']))
            {
                $qbs->andWhere("por.primaryRegion = :primaryRegion")
                ->setParameter("primaryRegion", $fields['primaryRegion']);
            }
            if($fields['primaryType'] != 0 && !empty($fields['primaryType']))
            {
                $qbs->andWhere("por.primaryType = :primaryType")
                ->setParameter("primaryType", $fields['primaryType']);
            }
            if($fields['tenure'] != '0' && !empty($fields['tenure']))
            {
                $qbs->andWhere("por.tenure = :tenure")
                ->setParameter("tenure", $fields['tenure']);
            }
            if(!empty($fields['minListingDate'] ))
            {
                $qbs->andWhere("list.listingDate >= :minListingDate")
                ->setParameter("minListingDate", $fields['minListingDate']);
            }
            if(!empty($fields['maxListingDate'] ))
            {
                $qbs->andWhere("list.listingDate <= :maxListingDate")
                ->setParameter("maxListingDate", $fields['maxListingDate']);
            }
            if(!empty($fields['minCloseDate']))
            {
                $qbs->andWhere("list.closeDate >= :minCloseDate")
                ->setParameter("minCloseDate", $fields['minCloseDate']);
            }
            if(!empty($fields['maxCloseDate']))
            {
                $qbs->andWhere("list.closeDate <= :maxCloseDate")
                ->setParameter("maxCloseDate", $fields['maxCloseDate']);
            }
            $qbs->orderBy("list.id","ASC");

            return $qbs;
    }
    
    public function getQueryForOppartunityRental($qbr, $fields) {
            
            $qbr ->select(
                "por as property",
                "le as Lease",
                "act as activity",
                "rtype.description as rtype_description",
                "react.id as react_id",
                "react.type as react_type",
                "react.date as react_date",
                "sug.id as sug_id",
                "suEN.id as suEN_id",
                "suEN.name as suEN_name"
            )  
            ->from("PropertyBundle:Property", "por")
            ->innerJoin("PropertyBundle:Lease", "le", Join::WITH, "por.id = le.property")
            ->innerJoin("le.relationships",'sug')
            ->innerJoin("sug.type",'rtype')
            ->innerJoin("sug.target",'suEN')
            ->innerJoin("PropertyBundle:RentalActivity", "react", Join::WITH, "le.id = react.lease")
            ->innerJoin("PropertyBundle:ActivityDate", "act", Join::WITH, "act.objectId = react.id");
            if($fields['custExist']  === 'true')
            {
                $qbr->InnerJoin("PropertyBundle:PreviousJobs","pj" , Join::WITH, "por.id = pj.property_id");
            }
            $qbr->where("por.latitude != 0")
            ->andWhere("por.longitude != 0")
            ->andWhere("por.status = 'active'")
            ->andWhere("act.type = 'rental_activity'");
            
            if($fields['custExist']  === 'true')
            {
                $qbr->andWhere("por.id = pj.property_id");
            }
            if($fields['primaryClass'] != 0 && !empty($fields['primaryClass']))
            {
                $qbr->andWhere("por.primaryClassification = :primaryClass ")
                ->setParameter("primaryClass", $fields['primaryClass']);
            }
            if($fields['primaryRegion'] != 0 && !empty($fields['primaryRegion']))
            {
                $qbr->andWhere("por.primaryRegion = :primaryRegion")
                ->setParameter("primaryRegion", $fields['primaryRegion']);
            }
            if($fields['primaryType'] != 0 && !empty($fields['primaryType']))
            {
                $qbr->andWhere("por.primaryType = :primaryType")
                ->setParameter("primaryType", $fields['primaryType']);
            }
            if($fields['tenure'] != '0' && !empty($fields['tenure']))
            {
                $qbr->andWhere("por.tenure = :tenure")
                ->setParameter("tenure", $fields['tenure']);
            }
            if(!empty($fields['minDate'] ))
            {   
                $qbr->andWhere("act.date >= :minDate")
                ->setParameter("minDate", $fields['minDate']);
            }
            if(!empty($fields['maxDate'] ))
            {
                $qbr->andWhere("act.date <= :maxDate")
                ->setParameter("maxDate", $fields['maxDate']);
            }
            if(!empty($fields['minResolvedDate']))
            {
                $qbr->andWhere("act.resolvedTimestamp >= :minResolvedDate")
                ->setParameter("minResolvedDate", $fields['minResolvedDate']);
            }
            if(!empty($fields['maxResolvedDate']))
            {
                $qbr->andWhere("act.resolvedTimestamp <= :maxResolvedDate")
                ->setParameter("maxResolvedDate", $fields['maxResolvedDate']);
            }
            if( $fields['rentType'] != "0" && !empty($fields['rentType']))
            {
                $qbr->andWhere("react.type = :rentType")
                ->setParameter("rentType", $fields['rentType']);
            }
            $qbr->orderBy("le.id","ASC");

            return $qbr;
    }

    public function getQueryForOpportunityListing($qbr, $fields) {
        
        $qbr ->select(
                    "por as property",
                    "list as listings",
                    "sug.id as sug_id",
                    "rtype.description as rtype_description",
                    "suEN.id as suEN_id",
                    "suEN.name as suEN_name"
                )    
            ->from("PropertyBundle:Property", "por")
            ->innerJoin("PropertyBundle:Listing", "list", Join::WITH, "por.id = list.property")
            ->innerJoin("list.relationships",'sug')
            ->innerJoin("sug.type",'rtype')
            ->innerJoin("sug.target",'suEN')
            ->innerJoin("PropertyBundle:ActivityDate", "act", Join::WITH, "act.objectId = list.id");
            
            if($fields['custExist']  === 'true')
            {
                $qbr->InnerJoin("PropertyBundle:PreviousJobs","pj" , Join::WITH, "por.id = pj.property_id");
            }
            
            $qbr->Where("act.type = 'listing_activity'");
            
            if($fields['custExist']  === 'true')
            {
                $qbr->andWhere("por.id = pj.property_id");
            }
            if($fields['primaryClass'] != 0 && !empty($fields['primaryClass']))
            {
                $qbr->andWhere("por.primaryClassification = :primaryClass ")
                ->setParameter("primaryClass", $fields['primaryClass']);
            }
            if($fields['primaryRegion'] != 0 && !empty($fields['primaryRegion']))
            {
                $qbr->andWhere("por.primaryRegion = :primaryRegion")
                ->setParameter("primaryRegion", $fields['primaryRegion']);
            }
            if($fields['primaryType'] != 0 && !empty($fields['primaryType']))
            {
                $qbr->andWhere("por.primaryType = :primaryType")
                ->setParameter("primaryType", $fields['primaryType']);
            }
            if($fields['tenure'] != '0' && !empty($fields['tenure']))
            {
                $qbr->andWhere("por.tenure = :tenure")
                ->setParameter("tenure", $fields['tenure']);
            }
            if(!empty($fields['minDate'] ))
            {
                $qbr->andWhere("act.date >= :minDate")
                ->setParameter("minDate", $fields['minDate']);
            }
            if(!empty($fields['maxDate'] ))
            {
                $qbr->andWhere("act.date <= :maxDate")
                ->setParameter("maxDate", $fields['maxDate']);
            }
            if(!empty($fields['minResolvedDate']))
            {
                $qbr->andWhere("act.resolvedTimestamp >= :minResolvedDate")
                ->setParameter("minResolvedDate", $fields['minResolvedDate']);
            }
            if(!empty($fields['maxResolvedDate']))
            {
                $qbr->andWhere("act.resolvedTimestamp <= :maxResolvedDate")
                ->setParameter("maxResolvedDate", $fields['maxResolvedDate']);
            }
            if( $fields['rentType'] != "0" && !empty($fields['rentType']))
            {
                $qbr->andWhere("list.method = :rentType")
                ->setParameter("rentType", $fields['rentType']);
            }
            $qbr->orderBy("list.id","ASC");

            return $qbr;
    }
    
    
     public function getQueryForOpportunityCustoms($qbr, $fields) {
        $qbr ->select(
                "p as property",
                "act as activity",
                "sug.id as sug_id",
                "rtype.description as rtype_description",
                "suEN.id as suEN_id",
                "suEN.name as suEN_name",
                "suEN.phoneNumber as suEN_phoneNumber",
                "custAct.description as custAct_description"
            )    
            ->from("PropertyBundle:Property", "p")
            ->innerJoin("p.relationships",'sug')
            ->innerJoin("sug.type",'rtype')
            ->innerJoin("sug.target",'suEN')
            ->innerJoin("PropertyBundle:ActivityDate", "act", Join::WITH, "act.property = p.id")
            ->innerJoin("PropertyBundle:CustomActivityType", "custAct", Join::WITH, "custAct.id = act.customType");
            
            if($fields['custExist']  === 'true')
            {
                $qbr->InnerJoin("PropertyBundle:PreviousJobs","pj" , Join::WITH, "p.id = pj.property_id");
            }
                
            $qbr->where("p.latitude != 0")
            ->andWhere("p.longitude != 0")
            ->andWhere("act.type = 'custom_activity'");
            
            if($fields['custExist']  === 'true')
            {
                $qbr->andWhere("p.id = pj.property_id");
            }
            if($fields['primaryClass'] != 0 && !empty($fields['primaryClass']))
            {
                $qbr->andWhere("p.primaryClassification = :primaryClass ")
                ->setParameter("primaryClass", $fields['primaryClass']);
            }
            if($fields['primaryRegion'] != 0 && !empty($fields['primaryRegion']))
            {
                $qbr->andWhere("p.primaryRegion = :primaryRegion")
                ->setParameter("primaryRegion", $fields['primaryRegion']);
            }
            if($fields['primaryType'] != 0 && !empty($fields['primaryType']))
            {
                $qbr->andWhere("p.primaryType = :primaryType")
                ->setParameter("primaryType", $fields['primaryType']);
            }
            if($fields['tenure'] != '0' && !empty($fields['tenure']))
            {
                $qbr->andWhere("p.tenure = :tenure")
                ->setParameter("tenure", $fields['tenure']);
            }
            if(!empty($fields['minDate'] ))
            {
                $qbr->andWhere("act.date >= :minDate")
                ->setParameter("minDate", $fields['minDate']);
            }
            if(!empty($fields['maxDate'] ))
            {
                $qbr->andWhere("act.date <= :maxDate")
                ->setParameter("maxDate", $fields['maxDate']);
            }
            if(!empty($fields['minResolvedDate']))
            {
                $qbr->andWhere("act.resolvedTimestamp >= :minResolvedDate")
                ->setParameter("minResolvedDate", $fields['minResolvedDate']);
            }
            if(!empty($fields['maxResolvedDate']))
            {
                $qbr->andWhere("act.resolvedTimestamp <= :maxResolvedDate")
                ->setParameter("maxResolvedDate", $fields['maxResolvedDate']);
            }
            if( $fields['rentType'] != "0" && !empty($fields['rentType']))
            {
                $qbr->andWhere("act.customType = :rentType")
                ->setParameter("rentType", $fields['rentType']);
            }
            $qbr->orderBy("p.id","ASC");

            return $qbr;
    }
}

