<?php

/**
 * Class provides methods for managing the offers.
 * @author Jakub Konieczka
 *
 */
class Offers{
    
	/**
	 * Creates a offer object on the basis of data from the database.
	 * @param array $row
	 * @return Offer
	 */
	protected static function BuildOffer($row){
		$ofe = new Offer($row['id_lng'],$row['id'],$row['status'],$row['object'],$row['rent'],$row['symbol'],$row['original'],$row['province'],$row['district'],
			$row['location'],$row['quarter'],$row['region'],$row['street'],$row['floor'],$row['price'],$row['price_square'],$row['rooms_no'],
			$row['area'],$row['latitude'],$row['longitude'],$row['building_technology'],$row['construction_material'],$row['construction_status'],
			$row['building_type'],$row['agents_id'],$row['creation_date'],$row['modification_date'],$row['investments_buildings_id'],$row['country'],
            $row['floor_no'],$row['year_of_construction'],$row['house_type'],$row['first_page'],$row['object_type'],$row['contract_type'],$row['visits_no'],
            $row['legal_status'],$row['ownership_status'],$row['furnishings'],$row['field_area'],$row['change_status'],$row['notices'],$row['notices_property'],
            $row['video_link'], $row['no_commission'], $row['expiration_date'], $row['has_swfs'], $row['has_movs'], $row['has_photos'], $row['has_pans'], $row['has_maps'],
            $row['has_proj'], $row['loc_as_commune']);
        if (isset($row['description_synonim'])) {
            $ofe->SetDescriptionSynonim($row['description_synonim']);
        }
                
		return $ofe;
	}

    /**
	 * Save all offer dynamic properties values to database.
	 * @param Offer $offer
     * @param bool $isNew
	 */
	protected static function SaveProperties(Offer $offer, $isNew){
		$lst = array();
        if(!$isNew){
            $query = "SELECT id, properties_id, value FROM #S#offers_properties WHERE offers_id=? AND offers_id_lng=?";
            $result = DataBase::GetDbInstance()->ExecuteQueryWithParams($query, array((int) $offer->GetId(), (int) $offer->GetIdLng()));
            while($row = DataBase::GetDbInstance()->FetchArray($result)){
                $lst[$row['properties_id']][] = array("id" => $row['id'], "value" => $row['value']);
            }
        }
		foreach($offer->data as $key => $value){
			$prop = Properties::GetWithAddProperty($key);          
			Properties::SavePropertyValue($prop, $offer, $value, $isNew, $lst);
		}
	}
    
    /**
     * Prepare filters to GetQuery
     * @param mixed $filters
     * @return array
     */
	protected static function PrepareFilters($filters = null){	
        $tab_with_numeric_value= array("Id","IdLng","PierwszaStrona","floor_noFrom","floor_noTo","RokBudowyOd","RokBudowyDo","rooms_no","rooms_noFrom","rooms_noTo","floorFrom","floorTo","original","rent","display_number","department");
		$tab_with_float_value = array("field_areaFrom","field_areaTo","priceFrom","priceTo","price_squareFrom","price_squareTo","areaFrom","areaTo");
		if($filters == null) return $filters;
		$newFilters = array();
		foreach ($filters as $key => $value){
			if(!is_array($value)){ 
                if(array_search($key, $tab_with_numeric_value)){
                    if(is_numeric($value)) $newFilters[$key]= (int) trim($value,'\'"');
                }else if(array_search($key, $tab_with_float_value)) {
                    if(is_numeric($value)) $newFilters[$key]= (float) trim($value,'\'"');
                }else{
                    $newFilters[$key] = trim($value,'\'"');
                }
            }else{ $newFilters[$key] = $value;}
			switch ($key) {	
				
				case "Kategorie": 
				case "construction_statuses":
				case "districts":
				case "locations":
				case "objects":
				case "quarters":
				case "building_types": 
                case "house_types":
                case "object_types":
                case "field_destiny":
                case "local_destiny":
                case "hall_destiny":
                case "kitchen_type":
                case "heating":
				case "map_bounds":
                case "noquarters": array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value)); break;
				case "ids":
                case "departments":
				case "not_agents":
                case "not_ids": array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value, true)); break;
				
				case "locations_or_quarters": array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value), self::prepareStringToArray($value)); break; 
				case "quarters_or_region":	array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value), self::prepareStringToArray($value)); break;
				case "locations_or_quarters_or_region":	array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value), self::prepareStringToArray($value), self::prepareStringToArray($value)); break;
				case "districts_or_locations_or_quarters_or_region": array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value), self::prepareStringToArray($value), self::prepareStringToArray($value), self::prepareStringToArray($value)); break;
				case "districts_or_locations_or_quarters_or_region_or_street": array_pop($newFilters); $newFilters = array_merge($newFilters, self::prepareStringToArray($value), self::prepareStringToArray($value), self::prepareStringToArray($value), self::prepareStringToArray($value),self::prepareStringToArray($value)); break; ;break;
				case "location_or_location": unset($newFilters['location_or_location']);$newFilters = array_merge($newFilters, self::prepareArraysToArray($value)); break;
				
				case "wylacznosc": array_pop($newFilters);$newFilters = array_merge($newFilters, self::prepareStringToArray("%".$value."%"));break;				
				case "pola_opisowe": array_pop($newFilters);$newFilters = array_merge($newFilters, array_fill(0, 2, "%".$value."%"));break;
				
				case "IloscOdslonWWWSort":
                case "photos":
				case "projs":
                case "video_swf":
                case "virtual_visit":
                case "without_virtual_visit":
                case "zeroprow": 
                case "zamiana":
                case "on_map":
                case "hasUwagiOpis":
				case "SortNaWylacznosc":
				case "not_mls":
                case "NajpierwIdekiOddzialow":array_pop($newFilters); break;
                case "properties":                	
                	array_pop($newFilters);
                	$allValues = array();
                    
                	foreach($value as $propValues){
                		$allValues = array_merge($allValues, is_array($propValues)?$propValues:explode(',', $propValues));
                	}	
                	
                	$newFilters = array_merge($newFilters, $allValues);
                	break;
			}
		}
		return $newFilters;
	}
    
    /**
     * Converts array or string to GetQuery
     * @param mixed $value
     * @return string
     */
	protected static function prepareStringToBind($value){
        if(is_array($value)) $arr = $value;
        else $arr = explode(',', $value);
		return implode(',', array_fill(0, count($arr), '?')); 
	}
    
	/**
     * Converts array or string to GetQuery
     * @param mixed $value
     * @return string
     */
	protected static function prepareArrayToBind($filters){
		$return = ' 1=0 ';
		
		foreach($filters as $filtrs){
			
			$query = ' 1=1 ';
			if(isset($filtrs['district']))
				$query .= ' AND o.district = ? ';
			if(isset($filtrs['location']))
				$query .= ' AND o.location = ? ';
			if(isset($filtrs['quarter']))
				$query .= ' AND o.quarter = ? ';
			if(isset($filtrs['region']))
				$query .= ' AND o.region = ? ';
			if(isset($filtrs['street']))
				$query .= ' AND o.street = ? ';
			
			$query = ' OR ( '.$query.' )';
			$return .= $query;
		}
		$return = '('.$return.')';
		return $return;
	}
	/**
     * Converts array or string to GetQuery
     * @param mixed $value
     * @return string[]
     */
    protected static function prepareArraysToArray($filters){
		$return = array();
		
		foreach($filters as $filtrs){
			if(isset($filtrs['district']))
				$return[] = trim($filtrs['district']);
			if(isset($filtrs['location']))
				$return[] = trim($filtrs['location']);
			if(isset($filtrs['quarter']))
				$return[] = trim($filtrs['quarter']);
			if(isset($filtrs['region']))
				$return[] = trim($filtrs['region']);
			if(isset($filtrs['street']))
				$return[] = trim($filtrs['street']);
		}
		
		return $return;
	}
	/**
     * Converts array or string to GetQuery
     * @param mixed $value
     * @return string[]
     */
	protected static function prepareStringToArray($value){		
        if(is_array($value)) $arr=$value;
		else $arr = explode(',', $value);
		$newArr = array();
		foreach($arr as $value){
			if($numeric && is_int(trim($value, "' "))) $newArr[] = (int) trim($value, "' ");
            else $newArr[] = trim($value, "' ");
		}
		return $newArr;
	}
	
    /**
     * Prepares properties to GetQuery
     * @param array $value
     * @return string
     */
	protected static function preparePropertiesToQuery($value){
		$i = 1;
		$query = '';
		foreach($value as $propValues){
			$query .= " AND op$i.value IN(".self::prepareStringToBind($propValues).")";
			$i++;
		}
		return $query;
	}
    
    
	/**
	* Returns cleared value for SQL query.
	* @param string $value
	* @return string
	*/
	protected static function prepareSort($sort){	
		$sort = strtolower($sort);
		$orderbyArray = array('id', 'id_lng', 'status', 'object', 'rent', 'symbol', 'original', 'province', 'district', 'location', 'quarter', 'region', 'street', 'floor', 'price', 'price_square', 'rooms_no', 'area', 'latitude', 'longitude', 'building_technology', 'construction_material', 'construction_status', 'building_type', 'agents_id', 'investments_buildings_id', 'creation_date', 'modification_date', 'description_synonim', 'display_number');
		$destArray = array('asc', 'desc');
		
		$exp_sort = explode(',', $sort);
		
		$return = '';
		foreach($exp_sort as $value){
            $exp = explode(' ', trim($value));
            $orderby = 'id';
            $dest = 'desc';
            if(isset($exp[0]) && in_array($exp[0], $orderbyArray)) $orderby = $exp[0];
            if(isset($exp[1]) && in_array(strtolower($exp[1]), $destArray)) $dest = $exp[1];
            $return .= $orderby.' '.$dest.',';
		}
		return trim($return, ',');		
	}
    
    /**
     * Creates query string from given params
     * @param string $select
     * @param string $sorting
     * @param array $filters
     * @return string
     */
	protected static function GetQuery($select = "SELECT * ", $sorting = "", $filters = null, $groupBy = ''){
		$query = $select . " FROM #S#offers AS o ";
        
        if ($filters != null && (
				array_key_exists("department", $filters) 
				|| array_key_exists("departments", $filters) 
				|| array_key_exists("NajpierwIdekiOddzialow",$filters) 
				|| array_key_exists("not_agents",$filters)
				|| array_key_exists("not_mls",$filters)
				)) {
			$query .= " LEFT JOIN #S#agents AS a ON o.agents_id=a.id ";
		}
		
		if(array_key_exists('properties', $filters)){
			$i = 1;
			foreach($filters['properties'] as $name=>$values){
				$query .= " INNER JOIN #S#offers_properties AS op$i ON (o.id = op$i.offers_id AND o.id_lng = op$i.offers_id_lng AND op$i.properties_id = ".OffersHelper::getProps($name).")";
				$i++;
			}	
            if($groupBy === '') $groupBy = 'o.id';
		}
		
		$query .= " WHERE 1=1 ";
		if($filters != null){
			foreach ($filters as $key => $value){
				switch ($key) {
					case "country": $query.= " AND o.country = ?";break;
					case "floor_noFrom": $query.= " AND o.floor_no >= ?";break;
					case "floor_noTo": $query .= " AND o.floor_no <= ?";break;
					case "RokBudowyOd": $query .= " AND CAST(o.year_of_construction AS SIGNED) >= ?";break;
					case "RokBudowyDo": $query .= " AND CAST(o.year_of_construction AS SIGNED) <= ?";break;
					case "PierwszaStrona" : $query .= " AND o.first_page = ?";break;
					case "house_types": $query .= " AND o.house_type IN (".self::prepareStringToBind($value).")";break;
					case "object_types": $query .= " AND o.object_type IN (".self::prepareStringToBind($value).")";break;
					case "wylacznosc": $query .= " AND o.contract_type LIKE ?";break;
					case "IloscOdslonWWWSort" : $query .= " AND o.visits_no ";break;
					case "StatusWlasnosci" : $query .= " AND o.ownership_status = ?";break;
					case "StanPrawny" : $query .= " AND o.legal_status = ?";break;
					case "umeblowanie": $query .= " AND o.furnishings = ?";break;
					case "field_areaFrom": $query .= " AND o.field_area >= ?";break;
					case "field_areaTo": $query .= " AND o.field_area <= ?";break;
					case "zamiana": $query .= " AND o.change_status = 1";break;
					case "pola_opisowe":$query .= " AND (o.notices LIKE ? OR o.notices_property LIKE ?)";break;
					case "IdLng": $query .= " AND o.id_lng=?"; break;
                    case "status": $query .= " AND o.status = ?"; break;
                    case "statuses": $query .= " AND o.status IN (".self::prepareStringToBind($value).")";break;
					case "symbol": $query .= " AND o.symbol LIKE ?"; break;					
					case "province": $query .= " AND o.province = ?"; break;
					case "district": $query .= " AND o.district = ?"; break;
					case "location": $query .= " AND o.location = ?"; break;
					case "object": $query .= " AND o.object = ?"; break;
					case "quarter": $query .= " AND o.quarter = ?"; break;
					case "region": $query .= " AND o.region = ?"; break;
					case "street": $query .= " AND o.street LIKE ?"; break;
					case "building_technology": $query .= " AND o.building_technology = ?"; break;
					case "construction_material": $query .= " AND o.construction_material = ?"; break;
					case "construction_status": $query .= " AND o.construction_status = ?"; break;
					case "construction_statuses": $query .= " AND o.construction_status IN (".self::prepareStringToBind($value).")";break;
					case "building_type": $query .= " AND o.building_type = ?"; break;					
					case "priceFrom": $query .= " AND o.price >= ?"; break;
					case "priceTo": $query .= " AND o.price <= ?"; break;
					case "price_squareFrom": $query .= " AND o.price_square >= ?"; break;
					case "price_squareTo": $query .= " AND o.price_square <= ?"; break;
					case "rooms_no": $query .= " AND o.rooms_no = ?"; break;
					case "rooms_noFrom": $query .= " AND o.rooms_no >= ?"; break;
					case "rooms_noTo": $query .= " AND o.rooms_no <= ?"; break;
					case "areaFrom": $query .= " AND o.area >= ?"; break;
					case "areaTo": $query .= " AND o.area <= ?"; break;
					case "floorFrom": $query .= " AND FLOOR(REPLACE(REPLACE(o.floor,'parter','0'),'p','')) >= ?"; break;
					case "floorTo": $query .= " AND FLOOR(REPLACE(REPLACE(o.floor,'parter','0'),'p','')) <= ?"; break;			
					case "districts": $query .= " AND o.district IN (".self::prepareStringToBind($value).")"; break;
					case "locations": $query .= " AND o.location IN (".self::prepareStringToBind($value).")"; break;
					case "objects": $query .= " AND o.object IN (".self::prepareStringToBind($value).")"; break;
					case "quarters": $query .= " AND o.quarter IN (".self::prepareStringToBind($value).")"; break;
					case "noquarters": $query .= " AND o.quarter NOT IN (".self::prepareStringToBind($value).")"; break;
                    case "locations_or_quarters": $query .= " AND (o.location IN (".self::prepareStringToBind($value).") OR o.quarter IN (".self::prepareStringToBind($value)."))"; break;
                    case "locations_or_quarters_or_region": $query .= " AND (o.location IN (".self::prepareStringToBind($value).") OR o.quarter IN (".self::prepareStringToBind($value).") OR o.region IN (".self::prepareStringToBind($value)."))"; break;
					case "quarters_or_region": $query .= " AND ( o.quarter IN (".self::prepareStringToBind($value).") OR o.region IN (".self::prepareStringToBind($value)."))"; break;
					case "districts_or_locations_or_quarters_or_region": $query .= " AND (o.district IN (".self::prepareStringToBind($value).") OR o.location IN (".self::prepareStringToBind($value).") OR o.quarter IN (".self::prepareStringToBind($value).") OR o.region IN (".self::prepareStringToBind($value)."))"; break;
					case "districts_or_locations_or_quarters_or_region_or_street": $query .= " AND (o.district IN (".self::prepareStringToBind($value).") OR o.location IN (".self::prepareStringToBind($value).") OR o.quarter IN (".self::prepareStringToBind($value).") OR o.region IN (".self::prepareStringToBind($value).") OR o.street IN(".self::prepareStringToBind($value).") )"; break;
					case "location_or_location": $query .= " AND ".self::prepareArrayToBind($value);break;
					case "building_types": $query .= " AND o.building_type IN (".self::prepareStringToBind($value).")"; break;					
					case "creationDateFrom": $query .= " AND o.creation_date >= ?"; break;
					case "creationDateTo": $query .= " AND o.creation_date <= ?"; break;
					case "modificationDateFrom": $query .= " AND o.modification_date >= ?"; break;
					case "modificationDateTo": $query .= " AND o.modification_date <= ?"; break;
					case "expirationDateFrom": $query .= " AND o.expiration_date >= ?"; break;
					case "video_swf": $query .=" AND (o.has_swfs=1 OR o.video_link IS NOT NULL)";break;
					case "virtual_visit": $query .= " AND o.has_swfs=1"; break;
					case "without_virtual_visit": $query .= " AND o.has_swfs=0"; break;
                    case "photos": $query .= " AND o.has_photos=1"; break;	
                    case "projs": $query .= " AND o.has_proj=1"; break;						
					case "ids": $query .= " AND o.id IN (".self::prepareStringToBind($value).")"; break;
					case "not_ids": $query .= " AND o.id NOT IN (".self::prepareStringToBind($value).")";break;					
					case "original": $query .= " AND o.original=?";break;
					case "on_map": $query .= " AND o.latitude > 0 AND o.longitude > 0";break;					
					case "map_bounds": $query .= " AND o.latitude < ? AND o.latitude > ? AND o.longitude < ? AND o.longitude > ?"; break;
                    case "department": $query.=" AND a.departments_id = ?"; break;
                    case "departments": $query.=" AND a.departments_id IN (".self::prepareStringToBind($value).")"; break;
					case "not_agents": $query.=" AND a.id NOT IN (".self::prepareStringToBind($value).")"; break;
					case "not_mls": $query .= " AND a.id NOT IN (". Agents::GetMLSAgentId() .")"; break;
                    case "zeroprow": $query.= " AND o.no_commission=1 ";break;			
                    case "hasUwagiOpis": $query .= " AND o.notices != ''"; break;		
                    case "kitchen_type": $query .= " AND o.id IN (SELECT ofr.offers_id FROM #S#offers_rooms ofr WHERE ofr.type IN (".self::prepareStringToBind($value).") )";break;
                    case "SortNaWylacznosc": break;
                    case "properties": $query .= self::preparePropertiesToQuery($value); break;
                    case "NajpierwIdekiOddzialow":break; //na potrzeby sortowania wg własnych oddziałów
					default: $query .= " AND o.$key=?"; break;					
				}
			}	
		}
		
		if($groupBy) $query .= " GROUP BY " . $groupBy;
		
		if($sorting != ""){		
			if ($filters != null && (array_key_exists("SortNaWylacznosc", $filters))) {
				$sorting = "id_lng ASC, CASE o.contract_type WHEN 'Umowa na wyłączność' THEN 0 ELSE 1 END ASC, o." . self::prepareSort($sorting);
				$query .= " ORDER BY ".$sorting;
			}elseif($filters != null && (array_key_exists("NajpierwIdekiOddzialow", $filters))){
                $sort = "id_lng ASC, CASE a.departments_id ";
                $odids = explode(",",$filters["NajpierwIdekiOddzialow"]);
                foreach($odids as $oid){
                    $sort.="WHEN ".$oid." THEN 0 ";
                }
                $sort .= " ELSE 1 END ASC, ";
                $sorting = $sort.self::prepareSort($sorting);
				$query .= " ORDER BY ".$sorting;
            }
			else $query .= " ORDER BY o.".self::prepareSort($sorting);
		}
		
		return $query;
	}
    
	/**
	 * Returns a offer object from the database by ID and ID LNG.
	 * @param int $id
     * @param int $idLng
	 * @return Offer
	 */
	public static function GetOffer($id, $idLng = 1045){
		$result = DataBase::GetDbInstance()->ExecuteQueryWithParams("SELECT * FROM #S#offers WHERE id=? AND id_lng=?", array((int) $id, (int) $idLng));
		if($result){
			$row = DataBase::GetDbInstance()->FetchArray($result);
			if($row) return self::BuildOffer($row);
			else return null;
		}else return null;
	}

	/**
	 * Add given offer object to database.
	 * @param Offer $ofe
	 */
	public static function AddOffer(Offer $ofe){
		$query = "INSERT INTO #S#offers (id, object, rent, symbol, original, province, district, location, quarter, region, street, floor, price,
			price_square, rooms_no, area, latitude, longitude, building_technology, construction_material, construction_status, building_type, agents_id,
			creation_date, modification_date, id_lng, status, description_synonim, country, floor_no, year_of_construction, house_type, first_page, object_type, 
            contract_type, visits_no, legal_status, ownership_status, furnishings, field_area, change_status, notices, notices_property, video_link, no_commission,
            expiration_date, has_swfs, has_movs, has_photos, has_pans, has_maps, has_proj, loc_as_commune)
			VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array($ofe->GetId(), $ofe->GetObject(), $ofe->GetRent(), $ofe->GetSymbol(), $ofe->GetOriginal(), $ofe->GetProvince(), $ofe->GetDistrict(),
			$ofe->GetLocation(), $ofe->GetQuarter(), $ofe->GetRegion(), $ofe->GetStreet(), $ofe->GetFloor(), $ofe->GetPrice(), $ofe->GetPriceSquare(), 
			$ofe->GetRoomsNo(), $ofe->GetArea(), $ofe->GetLatitude(), $ofe->GetLongitude(), $ofe->GetBuildingTechnology(), $ofe->GetConstructionMaterial(), 
			$ofe->GetConstructionStatus(), $ofe->GetBuildingType(), $ofe->GetAgentId(), $ofe->GetCreationDate(), $ofe->GetModificationDate(), $ofe->GetIdLng(), 
            $ofe->GetStatus(), $ofe->GetDescriptionSynonim(), $ofe->GetKraj(), $ofe->GetIloscPieter(), $ofe->GetRokBudowy(), $ofe->GetRodzajDomu(), $ofe->GetPierwszaStrona(),
            $ofe->GetRodzajObiektu(),$ofe->GetSposobPrzyjecia(),$ofe->GetIloscOdslonWWW(), $ofe->GetStanPrawny(), $ofe->GetStatusWlasnosci(), $ofe->GetUmeblowanieLista(), 
            $ofe->GetPowierzchniaDzialki(), $ofe->GetZamiana(), $ofe->GetUwagiOpis(), $ofe->GetUwagiNieruchomosc(), $ofe->GetVideoLink(), $ofe->GetNoCommission(), $ofe->GetExpirationDate(),
            $ofe->GetHasSwfs(), $ofe->GetHasMovs(), $ofe->GetHasPhotos(), $ofe->GetHasPans(), $ofe->GetHasMaps(), $ofe->GetHasProj(), $ofe->GetLocAsCommune());
		$result = DataBase::GetDbInstance()->ExecuteQueryWithParams($query, $params);
		
		self::SaveProperties($ofe, true);
	}
	
	/**
	 * Save given offer object (ofeNew) to database.
	 * @param Offer $ofeNew
	 */
	public static function EditOffer(Offer $ofeNew){
		$query = "UPDATE #S#offers SET object=?, rent=?, symbol=?, original=?, province=?, district=?, location=?, quarter=?, region=?, street=?, floor=?, price=?, price_square=?, rooms_no=?, 
            area=?, latitude=?, longitude=?, building_technology=?, construction_material=?, construction_status=?, building_type=?, agents_id=?, creation_date=?, modification_date=?, status=?, 
            description_synonim=?, country=?, floor_no=?, year_of_construction=?, house_type=?, first_page=?, object_type=?, contract_type=?, visits_no=?, legal_status=?, ownership_status=?, furnishings=?,
            field_area=?, change_status=?, notices=?, notices_property=?, video_link=?, no_commission=?, expiration_date=?, has_swfs=?, has_movs=?, has_photos=?, has_pans=?, has_maps=?, has_proj=?,
            loc_as_commune=? WHERE id=? AND id_lng=?;";
		$params = array($ofeNew->GetObject(), $ofeNew->GetRent(), $ofeNew->GetSymbol(), $ofeNew->GetOriginal(), $ofeNew->GetProvince(), $ofeNew->GetDistrict(), $ofeNew->GetLocation(), $ofeNew->GetQuarter(), 
            $ofeNew->GetRegion(), $ofeNew->GetStreet(), $ofeNew->GetFloor(), $ofeNew->GetPrice(), $ofeNew->GetPriceSquare(), $ofeNew->GetRoomsNo(), $ofeNew->GetArea(), $ofeNew->GetLatitude(), 
            $ofeNew->GetLongitude(), $ofeNew->GetBuildingTechnology(), $ofeNew->GetConstructionMaterial(), $ofeNew->GetConstructionStatus(), $ofeNew->GetBuildingType(), $ofeNew->GetAgentId(), 
            $ofeNew->GetCreationDate(), $ofeNew->GetModificationDate(), $ofeNew->GetStatus(), $ofeNew->GetDescriptionSynonim(), $ofeNew->GetKraj(), $ofeNew->GetIloscPieter(), $ofeNew->GetRokBudowy(), 
            $ofeNew->GetRodzajDomu(), $ofeNew->GetPierwszaStrona(), $ofeNew->GetRodzajObiektu(), $ofeNew->GetSposobPrzyjecia(), $ofeNew->GetIloscOdslonWWW(), $ofeNew->GetStanPrawny(), $ofeNew->GetStatusWlasnosci(), 
            $ofeNew->GetUmeblowanieLista(), $ofeNew->GetPowierzchniaDzialki(), $ofeNew->GetZamiana(), $ofeNew->GetUwagiOpis(), $ofeNew->GetUwagiNieruchomosc(), $ofeNew->GetVideoLink(), $ofeNew->GetNoCommission(),
            $ofeNew->GetExpirationDate(),$ofeNew->GetHasSwfs(), $ofeNew->GetHasMovs(), $ofeNew->GetHasPhotos(), $ofeNew->GetHasPans(), $ofeNew->GetHasMaps(), $ofeNew->GetHasProj(), $ofeNew->GetLocAsCommune(),
            (int) $ofeNew->GetId(), (int) $ofeNew->GetIdLng());
		$result = DataBase::GetDbInstance()->ExecuteQueryWithParams($query, $params);
		self::SaveProperties($ofeNew, false);
	}
    
	/**
	 * Add or edit if exists, given offer object.
	 * @param Offer $ofe
	 */
	public static function AddEditOffer(Offer $ofe){
		$o = self::GetOffer($ofe->GetId(), $ofe->GetIdLng());		
		if($o == null){
			self::AddOffer($ofe);
			return "A";
		}else{
            //delete generated photos
            if($o->Atrybut("ZeroProwizji") != $ofe->Atrybut("ZeroProwizji")){
                OfferPhotos::DeletePhotoFromDisk(0, $ofe->GetId());
            }
            self::EditOffer($ofe);
			return "E";
		}
	}
	
	/**
	 * Delete offer from database, given by ID.
	 * @param int $id
	 */
	public static function DeleteOffer($id){
		$offer = self::GetOffer($id);
		if($offer != null){
			OfferPhotos::DeletePhotos($id);
			$result = DataBase::GetDbInstance()->ExecuteQueryWithParams("DELETE FROM #S#offers_properties WHERE offers_id=?", array((int) $id));
			OfferRooms::DeleteRooms($id, null);
			$result = DataBase::GetDbInstance()->ExecuteQueryWithParams("DELETE FROM #S#offers WHERE id=?", array((int) $id));
			return "D";
		}	
	}

	/**
	 * Delete all unused offer properties values, that are no longer published.
	 * @param int $offerId
	 * @param int $offerLng
	 * @param array $propsIds
	 */
	public static function DeleteUnUseProperties($offerId, $offerLng, $propsIds){
		$params = array((int) $offerId, (int) $offerLng);
		if(count($propsIds) > 0){
			$inBind = implode(',', array_fill(0, count($propsIds), '?'));			
			$params = array_merge($params, $propsIds);
			$result = DataBase::GetDbInstance()->ExecuteQueryWithParams("DELETE FROM #S#offers_properties WHERE offers_id=? AND offers_id_lng=? AND properties_id NOT IN ($inBind)", $params);
		}else{
			$result = DataBase::GetDbInstance()->ExecuteQueryWithParams("DELETE FROM #S#offers_properties WHERE offers_id=? AND offers_id_lng=?", $params);
        }
	}
    
	/**
	 * Returns a list of offers in given language, take into account the filtering and sorting.
	 * @param RefreshEventArgs $args
     * @param int $idLng
	 * @return Offer[]
	 */
	public static function GetOffers(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
        $args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);
        $query_start = "SELECT COUNT(o.id)";
        if(array_key_exists('properties', $args->Filters)) $query_start = "SELECT COUNT(DISTINCT(o.id))";
        $query = self::GetQuery($query_start, "", $args->Filters, false);
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		$args->SetRowsCount($row[0]);
		
		$query = "SELECT o.id_lng, o.id, o.object, o.rent, o.symbol, o.original, o.province, o.district, o.location, o.quarter, o.region,
			o.street, o.floor, o.price, o.price_square, o.rooms_no, o.area, o.latitude, o.longitude, o.building_technology, o.status,
			o.construction_material, o.construction_status, o.building_type, o.agents_id, o.creation_date, o.modification_date, o.investments_buildings_id, o.description_synonim, 
            o.country, o.floor_no, o.year_of_construction, o.house_type, o.first_page, o.object_type, o.contract_type, 
            o.visits_no, o.legal_status, o.ownership_status, o.furnishings, o.field_area, o.change_status, o.notices, o.notices_property, o.video_link, o.no_commission, o.expiration_date,
            o.has_swfs, o.has_movs, o.has_photos, o.has_pans, o.has_maps, o.has_proj, o.loc_as_commune";
		$query = self::GetQuery($query, $args->Sorting, $args->Filters);
		$args->SetLimit($query);
		$list = array();
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		while($row = $db->FetchArray($result)){
			$list[count($list)] = self::BuildOffer($row);
		}
		return $list;
	}
	
	/**
	* Returns an array with offers minimum and maximum price in given language.
	* @param RefreshEventArgs $args
	* @param int $idLng
	* @return array(min, max)
	*/
	public static function GetOffersMinMaxPrice(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
		$args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);		
	
		$query = self::GetQuery("SELECT MIN(o.price), MAX(o.price) ", $args->Sorting, $args->Filters);		
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		return array($row[0], $row[1]);
	}
	
	/**
	* Returns an array with offers minimum and maximum rooms number in given language.
	* @param RefreshEventArgs $args
	* @param int $idLng
	* @return array(min, max)
	*/
	public static function GetOffersMinMaxRoomsNo(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
		$args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);
	
		$query = self::GetQuery("SELECT MIN(o.rooms_no), MAX(o.rooms_no) ", $args->Sorting, $args->Filters);
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		return array($row[0], $row[1]);
	}
	
	/**
	* Returns an array with offers minimum and maximum price/m2 in given language.
	* @param RefreshEventArgs $args
	* @param int $idLng
	* @return array(min, max)
	*/
	public static function GetOffersMinMaxPriceSquare(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
		$args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);
	
		$query = self::GetQuery("SELECT MIN(o.price_square), MAX(o.price_square) ", $args->Sorting, $args->Filters);
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		return array($row[0], $row[1]);
	}
	
	/**
	* Returns an array with offers minimum and maximum area in given language.
	* @param RefreshEventArgs $args
	* @param int $idLng
	* @return array(min, max)
	*/
	public static function GetOffersMinMaxArea(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
		$args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);
	
		$query = self::GetQuery("SELECT MIN(o.area), MAX(o.area) ", $args->Sorting, $args->Filters);
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		return array($row[0], $row[1]);
	}
	
	/**
	 * Returns an array with offers minimum and maximum field area in given language.
	 * @param RefreshEventArgs $args
	 * @param int $idLng
	 * @return array(min, max)
	 */
	public static function GetOffersMinMaxFieldArea(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
		$args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);
	
		$query = self::GetQuery("SELECT MIN(o.field_area), MAX(o.field_area) ", $args->Sorting, $args->Filters);
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		return array($row[0], $row[1]);
	}
	/**
	 * Returns an array with offers minimum and maximum field area in given language.
	 * @param RefreshEventArgs $args
	 * @param int $idLng
	 * @return array(min, max)
	 */
	public static function getDistrictsAndLocationsAndQuartersAndRegions($districts = null, $province = null, $idLng = 1045, $object = null, $rent = null, $building_types = null, $house_types = null, $field_destiny = null, $local_destiny = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			//$params = array($idLng, $province);		
			
			$query = "SELECT DISTINCT o.district, o.location, o.quarter, o.region FROM #S#offers o";
			//echo $query;die();
			if($field_destiny != null) $query .= " INNER JOIN offers_properties op1 ON o.id = op1.offers_id ";
			if($local_destiny != null) $query .= " INNER JOIN offers_properties op2 ON o.id = op2.offers_id ";
			
			$query .= " WHERE o.location IS NOT NULL AND o.id_lng=? ";
			
			if($province != null) $query .= " AND o.province=? ";
	                
			$i = 1;
			if($province) $filters = array((int) $idLng, $province);
			else $filters = array((int) $idLng);
	                
			if($districts != null) {
				$inBind = implode(',', array_fill(0, count($districts), '?'));			
				$query .= " AND o.district IN (".$inBind.") ";
				$filters = array_merge($filters, $districts);		
			}
	                
			if($object != null) {
				$query .= " AND o.object=?";
				$filters[] = $object;
				$i++;
			}
			
			if($rent === 0 || $rent === 1) {
				$query .= " AND o.rent=?";
		        $filters[] = $rent;
			}
			
			if($building_types !== null) {
				$query .= " AND o.building_type IN (".self::prepareStringToBind($building_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($building_types));
			}
			
			if($house_types !== null) {
				$query .= " AND o.house_type IN (".self::prepareStringToBind($house_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($house_types));			
			}
			
			if($field_destiny !== null) {
				$query .= " AND op1.properties_id = " .OffersHelper::getProps("PrzeznaczenieDzialkiSet"). " AND op1.value IN (".self::prepareStringToBind($field_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($field_destiny));
			}
			
			if($local_destiny !== null) {
				$query .= " AND op2.properties_id = ".OffersHelper::getProps("PrzeznaczenieLokalu")." AND op2.value IN (".self::prepareStringToBind($local_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($local_destiny));
			}
	                
			$query .= " ORDER BY o.location ASC";
	            
			if($filters != null) $result = $db->ExecuteQueryWithParams($query, $filters);
					
			$list = array();
			$i = 0;
			
			while($row = $db->FetchArray($result)){
				$list[$row[0]][$row[1]][$row[2]][$row[3]]=1;
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}

	    /* Returns an array with offers minimum and maximum field area in given language.
	 * @param RefreshEventArgs $args
	 * @param int $idLng
	 * @return array(min, max)
	 */
	public static function getDistrictsAndLocationsAndQuartersAndProvinces($districts = null, $province = null, $idLng = 1045, $object = null, $rent = null, $building_types = null, $house_types = null, $field_destiny = null, $local_destiny = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			//$params = array($idLng, $province);		
			
			$query = "SELECT DISTINCT o.province, o.location, o.quarter FROM #S#offers o";
			//echo $query;die();
			if($field_destiny != null) $query .= " INNER JOIN offers_properties op1 ON o.id = op1.offers_id ";
			if($local_destiny != null) $query .= " INNER JOIN offers_properties op2 ON o.id = op2.offers_id ";
			
			$query .= " WHERE o.location IS NOT NULL AND o.id_lng=? ";
    
			$i = 1;
            $filters = array((int) $idLng);
	                
			if($districts != null) {
				$inBind = implode(',', array_fill(0, count($districts), '?'));			
				$query .= " AND o.district IN (".$inBind.") ";
				$filters = array_merge($filters, $districts);		
			}
	                
			if($object != null) {
				$query .= " AND o.object=?";
				$filters[] = $object;
				$i++;
			}
			
			if($rent === 0 || $rent === 1) {
				$query .= " AND o.rent=?";
		        $filters[] = $rent;
			}
			
			if($building_types !== null) {
				$query .= " AND o.building_type IN (".self::prepareStringToBind($building_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($building_types));
			}
			
			if($house_types !== null) {
				$query .= " AND o.house_type IN (".self::prepareStringToBind($house_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($house_types));			
			}
			
			if($field_destiny !== null) {
				$query .= " AND op1.properties_id = " .OffersHelper::getProps("PrzeznaczenieDzialkiSet"). " AND op1.value IN (".self::prepareStringToBind($field_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($field_destiny));
			}
			
			if($local_destiny !== null) {
				$query .= " AND op2.properties_id = ".OffersHelper::getProps("PrzeznaczenieLokalu")." AND op2.value IN (".self::prepareStringToBind($local_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($local_destiny));
			}
	                
			$query .= " ORDER BY o.location ASC";
	            
			if($filters != null) $result = $db->ExecuteQueryWithParams($query, $filters);
					
			$list = array();
			$i = 0;
			
			while($row = $db->FetchArray($result)){
				$list[$row[0]][$row[1]][$row[2]]=1;
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}



	
	/**
	* Returns an array with offers distinct locations in given language.
	* @param RefreshEventArgs $args
	* @param int $idLng
	* @return array(min, max)
	*/
	public static function GetOffersLocations(RefreshEventArgs $args, $idLng = 1045){
		
		if(!($return = OffersHelper::getMethodResultCache())) {
			
			$db = DataBase::GetDbInstance();
			$args->Filters["IdLng"] = (int) $idLng;
			$filters = self::PrepareFilters($args->Filters);
		
			$query = self::GetQuery("SELECT DISTINCT(location) ", $args->Sorting, $args->Filters);
			$result = $db->ExecuteQueryWithParams($query, array_values($filters));
			$return = array();
			
			while($row = $db->FetchArray($result)){
				$return[] = $row[0];
			}
			OffersHelper::setMethodResultCache($return);		
		}
		return $return;
	}
	
	/**
	* Returns an array with offers distinct quarters in given language.
	* @param RefreshEventArgs $args
	* @param int $idLng
	* @return array(min, max)
	*/
	public static function GetOffersQuarters(RefreshEventArgs $args, $idLng = 1045){
		
		if(!($return = OffersHelper::getMethodResultCache())) {
			
			$db = DataBase::GetDbInstance();
			$args->Filters["IdLng"] = (int) $idLng;
			$filters = self::PrepareFilters($args->Filters);
		
			$query = self::GetQuery("SELECT DISTINCT(quarter) ", $args->Sorting, $args->Filters);
			$result = $db->ExecuteQueryWithParams($query, array_values($filters));
			$return = array();
		
			while($row = $db->FetchArray($result)){
				$return[] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($return);
			
		}
	
		return $return;
	}
	
	/**
	 * Returns a list of offer's ids, take into account the filtering and sorting.
	 * @param array $filters
	 * @param string $sort
	 * @return Offer[]
	 */
	public static function GetOffersId(RefreshEventArgs $args1, $sort=""){
		$db = DataBase::GetDbInstance();
		$filters = self::PrepareFilters($args1->Filters);
		$query = self::GetQuery("SELECT o.id ", $sort, $args1->Filters);
		$list = array();
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
		}
		return $list;
	}
	
	/**
	 * Returns a numer of offers in given language, take into account the filtering.
	 * @param RefreshEventArgs $args
     * @param int $idLng
	 * @return int
	 */
	public static function GetOffersCount(RefreshEventArgs $args, $idLng = 1045){
		$db = DataBase::GetDbInstance();
        if($args->Filters != null) $args->Filters["IdLng"] = (int) $idLng;
		$filters = self::PrepareFilters($args->Filters);
		$query = self::GetQuery("SELECT COUNT(*)", "", $args->Filters,"");
		$result = $db->ExecuteQueryWithParams($query, array_values($filters));
		$row = $db->FetchArray($result);
		return $row[0];
	}
    
	/**
	 * Returns a unique list of offer object.
     * @param string $country
     * @param bool $rent
	 * @return string[]
	 */
	public static function GetObjects($country="", $rent = false){
		
		if(!($list2 = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			$params = array();
			$query = "SELECT DISTINCT(object) FROM #S#offers o ";
	        
	        if($country!="") {
	        	$params[] = $country;
	            $query.=" WHERE o.country=?";
	        }
	        
	        if($rent != false) {
	        	$params[] = (int) $rent;
	        	$query.=" WHERE o.rent=?";
	        }
	        
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			$list1 = array();
			foreach($list as $value){
				switch ($value){
					case "Mieszkanie" : $list1[0] = $value; break;
					case "Dom" : $list1[1] = $value; break;
					case "Dzialka" : $list1[2] = $value; break;
					case "Lokal" : $list1[3] = $value; break;
					case "Hala" : $list1[4] = $value; break;
					case "Gospodarstwo" : $list1[5] = $value; break;
					case "Kamienica" : $list1[6] = $value; break;
					case "Obiekt" : $list1[7] = $value; break;
				}			
			}
			ksort($list1);
			$list2 = array();
			foreach($list1 as $value){
				$list2[$value] = $value;
			}
			
			OffersHelper::setMethodResultCache($list2);
			
		}
		
		
		return $list2;
	}
        
    /**
	 * Returns a list of offer object, and count them.
     * @param array[] $parameters
     * @param bool $showZero
     * @param string $country
     * @param int $idLng
     * @param bool $change
	 * @return string[]
	 */
	public static function GetObjectsCount($parameters = false, $showZero = false, $country="", $idLng = 1045, $change = false){
		if(!$parameters) $parameters = array();		
		
		$db = DataBase::GetDbInstance();
		$params = array();
		$query = "SELECT object, count(object) FROM #S#offers o WHERE o.id_lng = ?";
        $params[] = (int) $idLng;
        
        if ($country!=="") {
            $query.=" AND o.country=? ";
            $params[] = $country;
        }
        
        if ($change) {
            $query.=" AND o.change_status=1";
        }
		
		foreach($parameters as $index=>$value){
			$query .= " AND $index=?";
			$params[] = $value;
		}
		
		$query .=" GROUP BY object";		
		
		$result = $db->ExecuteQueryWithParams($query, $params);
		$list = array();
        
		while($row = $db->FetchArray($result)){
			$list[$row[0]] = $row[1];
		}
        
        $list1 = array();
        $list1['Mieszkanie'] = "0";
        $list1['Dom'] = "0";
        $list1['Dzialka'] = "0";
        $list1['Lokal'] = "0";
        $list1['Hala'] = "0";
        $list1['Gospodarstwo'] = "0";
        $list1['Kamienica'] = "0";
        $list1['Obiekt'] = "0";
        
		foreach($list1 as $value=>$count){
			if(isset($list[$value])) $list1[$value] = $list[$value];
			else if(!$showZero) unset($list1[$value]);					
		}
        
        return $list1;
	}
	
	/**
	 * Returns a unique list of countries
	 * @param int $idLng
     * @param bool $rent
     * @param mixed $object
	 * @return string[]
	 */
	public static function GetCountries($idLng = 1045, $rent = false, $object = false){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(country) FROM #S#offers WHERE country IS NOT NULL AND id_lng=?";
		
			if($rent !== false) {
				$query .= " AND rent=?"; $params[] = (int) $rent;
			}
			if($object !== false) {
				$query .= " AND object=?"; $params[] = $object;
			}
		
			$query .= " ORDER BY country ASC";
		
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
	
	/**
     * Returns a unique list of provinces used id offers.
     * @param int $idLng
     * @param bool $rent
     * @param mixed $object
     * @param mixed $country
     * @return string[]
     */
	public static function GetProvinces($idLng = 1045, $rent = false, $object = false, $country = false){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(province) FROM #S#offers WHERE province IS NOT NULL AND id_lng=?";
	
			if($rent) { $query .= " AND rent=?"; $params[] = (int) $rent; }
			if($object) { $query .= " AND object=?"; $params[] = $object; }
			if($country) { $query .= " AND country LIKE ?"; $params[] = $country; }
			
			$query .= " ORDER BY province ASC";
			
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
	
	/**
	 * Returns a unique list of provinces selected by country used id offers.
     * @param string $country
	 * @param int $idLng
	 * @return string[]
	 */
	public static function GetProvincesByCountry($country, $idLng = 1045){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			
			$db = DataBase::GetDbInstance();
			$params = array($country, (int) $idLng);
			$query = "SELECT DISTINCT(province) FROM #S#offers as o  WHERE o.province IS NOT NULL  AND o.country LIKE ? AND id_lng=? ORDER BY province ASC";
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
		}
		return $list;
	}

    /**
	 * Returns Provinces from Districtss offers
	 * @param mixed $districts optional district to look for provinces
	 * @return string[]
	 */
	public static function GetProvincesFromDistricts($district = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array();
			$query = "SELECT DISTINCT(province) FROM #S#offers WHERE province IS NOT NULL ";
	        if($district != null){
	            $query .= " AND district=? ";
	            $params[] = $district;
	        }

			$query .= " ORDER BY province ASC LIMIT 0, 1";
	               
			$result = $db->ExecuteQueryWithParams($query, $params);
			
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
        
    /**
	 * Returns Districts from Locations offers
	 * @param mixed $location optional location to look for districts
	 * @return string[]
	 */
	public static function GetDistrictsFromLocations($location = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array();
			$query = "SELECT DISTINCT(district) FROM #S#offers WHERE district IS NOT NULL ";
	        if($location != null){
	            $query .= " AND location=? ";
	            $params[] = $location;
	        }
	                
	        $filters = array();              
			$query .= " ORDER BY district ASC LIMIT 0, 1";
	               
			$result = $db->ExecuteQueryWithParams($query, $params);
			
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
        
	/**
	 * Returns a unique list of districts used id offers.
	 * @param string $province optional province to look for districts
     * @param int $idLng
     * @param bool $object
     * @param bool $rent
	 * @return string[]
	 */
	public static function GetDistricts($province = null, $idLng = 1045, $object = null, $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(district) FROM #S#offers WHERE district IS NOT NULL AND id_lng=?";
			
			if($province != null){
				$query .= " AND province=?";
				$params[] = $province;
			}
	                
	                
			if($object != null) {
				$query .= " AND object=?";
				$params[] = $object; 
			}
			
			if($rent !== null) {
				$query .= " AND rent=?";
				$params[] = (int) $rent;  
			}                
	                
			$query .= " ORDER BY district ASC";
	                
			$result = $db->ExecuteQueryWithParams($query, $params);
			
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		
		return $list;
	}
	
	/**
	 * Return a object location of collection locations, used name.
     * @param string $location optional location to look for locations
     * @param int $idLng
	 * @return string
	 */
    public static function GetLocationLp($location = null, $idLng = 1045){
        $db = DataBase::GetDbInstance();
        $params = array($location, (int) $idLng);
		$query = "SELECT location FROM #S#offers WHERE location LIKE  ? AND id_lng=? LIMIT 0,1";
                
        $result = $db->ExecuteQueryWithParams($query, $params);
		$list = array();
		while($row = $db->FetchArray($result)){
			$list[count($list)] = $row[0];
		}
		return $list;
    }
        
    /**
	 * Return a object quarter of collection quarters, used name.
     * @param string $quarter optional quarter to look for quarters
     * @param int $idLng
	 * @return string
	 */
    public static function GetQuarterLp($quarter = null, $idLng = 1045){
        $db = DataBase::GetDbInstance();
        $params = array($quarter, (int) $idLng);
		$query = "SELECT quarter FROM #S#offers WHERE quarter LIKE ? AND id_lng=? LIMIT 0,1";
                
        $result = $db->ExecuteQueryWithParams($query, $params);
		$list = array();
		while($row = $db->FetchArray($result)){
			$list[count($list)] = $row[0];
		}
		return $list;
    }
	
	/**
	 * Returns a unique list of locations used id offers.
	 * @param array $districts optional array of districts to look for locations
     * @param string $province optional province to look for locations
     * @param int $idLng
     * @param string $object
     * @param int $rent
     * @param mixed $building_types
     * @param mixed $house_types
     * @param mixed $field_destiny
     * @param mixed $local_destiny
	 * @return string[]
	 */
	public static function GetLocations($districts = null, $province = null, $idLng = 1045, $object = null, $rent = null, $building_types = null, $house_types = null, $field_destiny = null, $local_destiny = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			//$params = array($idLng, $province);		
			
			$query = "SELECT DISTINCT(o.location) FROM #S#offers o";
			
			if($field_destiny != null) $query .= " INNER JOIN offers_properties op1 ON o.id = op1.offers_id ";
			if($local_destiny != null) $query .= " INNER JOIN offers_properties op2 ON o.id = op2.offers_id ";
			
			$query .= " WHERE o.location IS NOT NULL AND o.id_lng=? ";
			
			if($province != null) $query .= " AND o.province=? ";
	                
			$i = 1;
			if($province) $filters = array((int) $idLng, $province);
			else $filters = array((int) $idLng);
	                
			if($districts != null) {
				$inBind = implode(',', array_fill(0, count($districts), '?'));			
				$query .= " AND o.district IN (".$inBind.") ";
				if (is_array($filters) && is_array($districts)) {
                    $filters = array_merge($filters, $districts);		
                } else {
                    $filters = array((int) $idLng, $districts);
                }		
			}
	                
			if($object != null) {
				$query .= " AND o.object=?";
				$filters[] = $object;
				$i++;
			}
			
			if($rent === 0 || $rent === 1) {
				$query .= " AND o.rent=?";
		        $filters[] = $rent;
			}
			
			if($building_types !== null) {
				$query .= " AND o.building_type IN (".self::prepareStringToBind($building_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($building_types));
			}
			
			if($house_types !== null) {
				$query .= " AND o.house_type IN (".self::prepareStringToBind($house_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($house_types));			
			}
			
			if($field_destiny !== null) {
				$query .= " AND op1.properties_id = " .OffersHelper::getProps("PrzeznaczenieDzialkiSet"). " AND op1.value IN (".self::prepareStringToBind($field_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($field_destiny));
			}
			
			if($local_destiny !== null) {
				$query .= " AND op2.properties_id = ".OffersHelper::getProps("PrzeznaczenieLokalu")." AND op2.value IN (".self::prepareStringToBind($local_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($local_destiny));
			}
	                
			$query .= " ORDER BY o.location ASC";
	            
			if($filters != null) $result = $db->ExecuteQueryWithParams($query, $filters);
					
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
	
	/**
	 * Returns a unique list of quarters as coummune locations used id offers.
	 * @param array $districts optional array of districts to look for locations
	 * @param string $province optional province to look for locations
	 * @param int $idLng
     * @param string $object
     * @param int $rent
     * @param mixed $building_types
     * @param mixed $house_types
     * @param mixed $field_destiny
     * @param mixed $local_destiny 
	 * @return string[]
	 */
	public static function GetLocationsAsCommune($districts = null, $province = null, $idLng = 1045, $object = null, $rent = null, $building_types = null, $house_types = null, $field_destiny = null, $local_destiny = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
		
			//$params = array($idLng, $province);
		
			$query = "SELECT DISTINCT(o.quarter) FROM #S#offers o";
		
			if($field_destiny != null) $query .= " INNER JOIN offers_properties op1 ON o.id = op1.offers_id";
			if($local_destiny != null) $query .= " INNER JOIN offers_properties op2 ON o.id = op2.offers_id";
		
			$query .= " WHERE o.location IS NOT NULL AND o.id_lng=? ";
		
			if($province != null) $query .= " AND o.province=? ";
		
			$i = 1;
			if($province) $filters = array((int) $idLng, $province);
			else $filters = array((int) $idLng);
		
			if($districts != null) {
				$inBind = implode(',', array_fill(0, count($districts), '?'));
				$query .= " AND o.district IN (".$inBind.") ";
				$filters = array_merge($filters, $districts);
			}
		
			if($object != null) {
				$query .= " AND o.object=?";
				$filters[] = $object;
				$i++;
			}
		
			if($rent === 0 || $rent === 1) {
				$query .= " AND o.rent=?";
				$filters[] = (int) $rent;
			}
		
			if($building_types !== null) {
				$query .= " AND o.building_type IN (".self::prepareStringToBind($building_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($building_types));
			}
		
			if($house_types !== null) {
				$query .= " AND o.house_type IN (".self::prepareStringToBind($house_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($house_types));
			}
		
			if($field_destiny !== null) {
				$query .= " AND op1.properties_id = " .OffersHelper::getProps("PrzeznaczenieDzialkiSet"). " AND op1.value IN (".self::prepareStringToBind($field_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($field_destiny));
			}
			
			if($local_destiny !== null) {
				$query .= " AND op2.properties_id = ".OffersHelper::getProps("PrzeznaczenieLokalu")." AND op2.value IN (".self::prepareStringToBind($local_destiny).")";
				$filters = array_merge($filters, self::prepareStringToArray($local_destiny));
			}
		
			$query .= " AND o.loc_as_commune = 1 ORDER BY o.location ASC";
		
			if($filters != null) $result = $db->ExecuteQueryWithParams($query, $filters);
		
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
	
	/**
     * Returns a unique list of streets.
	 * @param array $districts optional array of districts to look for locations
	 * @param string $province optional province to look for locations
	 * @param int $idLng
	 * @return string[]
	 */
	public static function GetStreets($location = null, $quarter = null, $idLng = 1045, $object = null, $rent = null, $region = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			$filters = array((int) $idLng);
			$query = "SELECT DISTINCT(o.street) FROM #S#offers o WHERE o.street IS NOT NULL AND o.id_lng=? ";
			if($location != null) { $query .= " AND o.location=? "; $filters[] = $location; }
			if($quarter != null) { $query .= " AND o.quarter=? "; $filters[] = $quarter; }
				
			if($object != null) {
				$query .= " AND o.object=?";
				$filters[] = $object;
			}
			if($rent !== null) {
				$query .= " AND o.rent=?";
				$filters[] = (int) $rent;
			}
			if($region != null) {
				$query .= " AND o.region=?";
				$filters[] = $region;
			}
		
			$query .= " ORDER BY o.street ASC";
		
			$result = $db->ExecuteQueryWithParams($query, $filters);
			
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);		
		}
		
		return $list;
	}
	
	/**
	 * Returns a unique list of quarters used in offers.
	 * @param array $locations optional array of locations to look for quarters
     * @param int $idLng
     * @param mixed $object
     * @param mixed $rent
     * @param mixed $building_types
	 * @return string[]
	 */
	public static function GetQuarters($locations = null, $idLng = 1045, $object = null, $rent = null, $building_types = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			$filters = array((int) $idLng);
			
			$query = "SELECT DISTINCT(o.quarter) FROM #S#offers o WHERE o.quarter IS NOT NULL AND o.id_lng=? ";
			
			if($locations != null) {
				
				$inBind = implode(',', array_fill(0, count($locations), '?'));
				$query .= " AND o.location IN (".$inBind.") ";

				if (is_array($filters) && is_array($locations)) {
                    $filters = array_merge($filters, $locations);		
                } else {
                    $filters = array((int) $idLng, $locations);
                }		
			}
			
			if($object != null){
				$query .= " AND o.object=?";                        
				$filters[] = $object;                        
			}
			
			if($rent !== null){
				$query .= " AND o.rent=?";                        
				$filters[] = (int) $rent;
			}
			
			if($building_types !== null) {
				$query .= " AND o.building_type IN (".self::prepareStringToBind($building_types).")";
				$filters = array_merge($filters, self::prepareStringToArray($building_types));
			}
	                
			$query .= " ORDER BY o.quarter ASC";
	
			$result = $db->ExecuteQueryWithParams($query, $filters);
			
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
		}
			
		return $list;
	}
	
	/**
	 * Returns a unique list of regions used in offers.
	 * @param array $quarters optional array of quarters to look for regions
     * @param int $idLng
	 * @return string[]
	 */
	public static function GetRegions($quarters = null, $idLng = 1045, $object = null, $rent = null, $locations = null){
		
			if(!($list = OffersHelper::getMethodResultCache())) {
			
			$db = DataBase::GetDbInstance();
			$filters = array((int) $idLng);
			$query = "SELECT DISTINCT(o.region) FROM #S#offers o WHERE o.region IS NOT NULL AND o.id_lng=? ";
	        	
			if($locations != null) {
				$inBind = implode(',', array_fill(0, count($locations), '?'));
				$query .= " AND o.location IN (".$inBind.") ";
				$filters = array_merge($filters, $locations);
			}
			
			if($quarters != null) {
				$inBind = implode(',', array_fill(0, count($quarters), '?'));
				$query .= " AND o.quarter IN (".$inBind.") ";
				$filters = array_merge($filters, $quarters);
			}
	        
			if($object != null){
				$query .= " AND o.object=?";                        
				$filters[] = $object;                        
			}
			
			if($rent !== null){
				$query .= " AND o.rent=?";                        
				$filters[] = (int) $rent;
			}
	        
			$query .= " ORDER BY o.region ASC";
			$result = $db->ExecuteQueryWithParams($query, $filters);
			        
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
			
		return $list;
	}
    
    /**
	 * Return a object region of collection locations, used name.
     * @param string $region optional location to look for locations
     * @param int $idLng
	 * @return string
	 */
    public static function GeRegionLp($region = null, $idLng = 1045){
        $db = DataBase::GetDbInstance();
        $params = array($region, (int) $idLng);

        $query = "SELECT o.region FROM #S#offers o WHERE o.region LIKE ? AND o.id_lng=? LIMIT 0,1";
        $result = $db->ExecuteQueryWithParams($query, $params);

        $list = array();
        while($row = $db->FetchArray($result)){
            $list[count($list)] = $row[0];
        }
        return $list;
    }
        
    /**
	 * Returns a unique list of building types for offer type (used in flats and locals).
     * @param int $idLng
     * @param int $rent 0 - sell, 1 - rent, default - all offers 
	 * @return string[]
	 */
	public static function GetBuildingTypes($idLng = 1045, $object = "", $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.building_type) FROM #S#offers o WHERE o.building_type IS NOT NULL AND o.id_lng=? ";
	        if($object != "") { $query.=" AND o.object=? "; $params[] = $object; }
	        if($rent !== null) { $query.=" AND o.rent=? "; $params[] = (int) $rent; }
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		
		return $list;
	}
    
    /**
    * Returns a unique list of kitchen type for offer type.
    * @param int $idLng
	* @return string[]
	*/
	public static function GetKitchenType($idLng = 1045){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(or.type) FROM #S#offers_rooms or WHERE or.type <> '' AND or.offers_id_lng=? ";
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
		}
		
		return $list;
	}
	
	/**
	 * Returns a unique list of house types (used in houses).
     * @param int $idLng
	 * @return string[]
	 */
	public static function GetHouseConstructionStatus($idLng = 1045){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.construction_status) FROM #S#offers o WHERE o.construction_status IS NOT NULL AND o.id_lng=?";
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
		}
		
		return $list;
	}
        
    /**
	 * Returns a unique list of construction materials (used in houses).
     * @param int $idLng
	 * @return string[]
	 */
	public static function GetConstructionMaterial($idLng = 1045){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.construction_material) FROM #S#offers o WHERE o.construction_material IS NOT NULL AND o.id_lng=?";
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
		}
		
		return $list;
	}
        
        /**
	 * Returns a unique list of building technology (used in houses).
        * @param int $idLng
	 * @return string[]
	 */
	public static function GetBuildingTechnology($idLng = 1045){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.building_technology) FROM #S#offers o WHERE o.building_technology IS NOT NULL AND o.id_lng=?";
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
			
		}
		return $list;
	}
    
    /**
	 * Returns a unique list of furnishings (used in apartments).
     * @param int $idLng
	 * @return string[]
	 */
	public static function GetApartmentFurnishings($idLng = 1045){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.furnishings) FROM #S#offers AS o WHERE o.id_lng=?";
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
		}
		return $list;
	}
        
     /**
	 * Returns a unique list of house constructions status (used in houses).
     * @param int $idLng
     * @param int $rent 0 - sell, 1 - rent, default - all offers
	 * @return string[]
	 */
	public static function GetHouseTypes($idLng = 1045, $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);		
			$query = "SELECT DISTINCT(o.house_type) FROM #S#offers AS o WHERE o.id_lng=?";
			if($rent !== null) { $query.=" AND o.rent=? "; $params[] = (int) $rent; }
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
			
		}
		return $list;
	}
        
    /**
	 * Returns a unique list of object types (used in objects).
     * @param int $idLng
     * @param int $rent 0 - sell, 1 - rent, default - all offers
	 * @return string[]
	 */
	public static function GetObjectTypes($idLng = 1045, $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.object_type) FROM #S#offers AS o WHERE o.id_lng=?";
			
	        if($rent !== null) { $query.=" AND o.rent=? "; $params[] = (int) $rent; }
	        
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
			
		}
		return $list;
	}
	
	/**
	* Returns a unique list of halls destiny (used in halls).
	* @param int $idLng
    * @param int $rent 0 - sell, 1 - rent, default - all offers
	* @return string[]
	*/
	public static function GetHallDestiny($idLng = 1045, $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			$query = 'SELECT DISTINCT(op.value) FROM #S#offers_properties AS op WHERE op.offers_id_lng=? AND op.properties_id='.OffersHelper::getProps("PrzeznaczenieHaliSet");
			
			if($rent !== null) { $query = 'SELECT DISTINCT(op.value) FROM #S#offers_properties AS op INNER JOIN #S#offers as o ON op.offers_id=o.id WHERE op.offers_id_lng=? AND op.properties_id='.OffersHelper::getProps("PrzeznaczenieHaliSet").' AND o.rent=? '; $params[] = (int) $rent; }
			
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);						
		}
				
		return $list;
	}
	
	/**
	 * Returns a unique list of parcels destiny (used in parcels).
     * @param int $idLng
     * @param int $rent 0 - sell, 1 - rent, default - all offers
	 * @return string[]
	 */
	public static function GetFieldDestiny($idLng = 1045, $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
			
			$query = 'SELECT DISTINCT(op.value) FROM #S#offers_properties AS op WHERE op.offers_id_lng=? AND op.properties_id='.OffersHelper::getProps("PrzeznaczenieDzialkiSet");
	
			if($rent !== null) { $query = 'SELECT DISTINCT(op.value) FROM #S#offers_properties AS op INNER JOIN #S#offers as o ON op.offers_id=o.id WHERE op.offers_id_lng=? AND op.properties_id='.OffersHelper::getProps("PrzeznaczenieDzialkiSet").' AND o.rent=?'; $params[] = (int) $rent; }
					
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);						
		}
		
			
		return $list;
			
	}
	
	/**
	 * Returns a unique list of premises destiny (used in premises).
     * @param int $idLng
     * @param int $rent 0 - sell, 1 - rent, default - all offers
	 * @return string[]
	 */
	public static function GetPremisesDestiny($idLng = 1045, $rent = null){
		
		if(!($list = OffersHelper::getMethodResultCache())) {
		
			$db = DataBase::GetDbInstance();
			
			$params = array((int) $idLng);
			$query = 'SELECT DISTINCT(op.value) FROM #S#offers_properties AS op WHERE op.offers_id_lng=? AND op.properties_id='.OffersHelper::getProps("PrzeznaczenieLokalu");
			
			if($rent !== null) { $query = 'SELECT DISTINCT(op.value) FROM #S#offers_properties AS op INNER JOIN #S#offers as o ON op.offers_id=o.id WHERE op.offers_id_lng=? AND op.properties_id='.OffersHelper::getProps("PrzeznaczenieLokalu").' AND o.rent=?'; $params[] = (int) $rent; }
			
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}

			OffersHelper::setMethodResultCache($list);
		}
		
		
		return $list;
	}

    /**
     * Returns a unique list of ownerships status.
     * @param int $idLng
     * @return string[]
     */
    public static function GetOwnershipsStatus($idLng = 1045){
		
    	if(!($list = OffersHelper::getMethodResultCache())) {
	    	$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
	        
			$query = "SELECT DISTINCT(o.ownership_status) FROM #S#offers AS o WHERE o.id_lng=?";
			
	        $result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
			
    	}
		return $list;
	}

    /**
     * Returns a unique list of legal status.
     * @param int $idLng
     * @return string[]
     */
    public static function GetLegalStatus($idLng = 1045){
		
    	if(!($list = OffersHelper::getMethodResultCache())) {
	    	$db = DataBase::GetDbInstance();
			$params = array((int) $idLng);
	        
			$query = "SELECT DISTINCT(o.legal_status) FROM #S#offers AS o WHERE o.id_lng=?";
			
	        $result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
    	}
		return $list;
	}
	
	/**
	 * Return an array of offers for given investment building.
	 * @param int $buildingId
     * @param int $idLng
	 * @return Offer[]
	 */
	public static function GetOffersInvestmentBuilding($buildingId, $idLng = 1045){
		$result = DataBase::GetDbInstance()->ExecuteQueryWithParams("SELECT * FROM #S#offers WHERE id_lng=? AND investments_buildings_id=? ORDER BY symbol ASC", array((int) $idLng, (int) $buildingId));
		$offers = array();
		$ndx = 0;
		while($row = DataBase::GetDbInstance()->FetchArray($result)){
			$offers[$ndx] = self::BuildOffer($row);
			$ndx++;			
		}
		return $offers;
	}
    
    /**
	 * Returns property objects from offers.
	 * @param string $name
	 * @return properties array
	 */
	public static function GetPropertiesFromOffers($name, $idLng=1045){

		if(!($list = OffersHelper::getMethodResultCache())) {
			$db = DataBase::GetDbInstance();
	
	        $params = array(OffersHelper::getProps($name), (int) $idLng);
	        $query = 'SELECT distinct(value) FROM #S#offers_properties op WHERE op.properties_id=? AND op.offers_id_lng=?';
	        $result = $db->ExecuteQueryWithParams($query, $params);
	
	        $list = array();
	        while($row = $db->FetchArray($result)){
	                $list[count($list)] = $row[0];
	        }
	        
	        OffersHelper::setMethodResultCache($list);
		}
        return $list;
	}
        
    /**
	 * Returns a unique list of house constructions status (used in houses).
     * @param int $idLng
     * @param string $object
     * @param string $location
     * @param int $rent
	 * @return string[]
	 */
    public static function GetHouseTypesLocations($idLng = 1045, $object='', $location='', $rent=null) {
		
    	if(!($list = OffersHelper::getMethodResultCache())) {
	        $db = DataBase::GetDbInstance();
	        $params = array((int) $idLng);
	        
	        $query = "SELECT DISTINCT(o.house_type) FROM #S#offers AS o WHERE o.id_lng=? ";
	                
	        if ($object <> '' ) {
	            $query .=  "AND o.object=? ";
	            $params[] = $object;
	        }
	        if ($location <> '' ) {
	            $query .=  "AND (o.location=? OR o.quarter=? OR o.region=?) ";
	            $params = array_merge($params, array($location, $location, $location));
	        }
	        if ($rent !== null ) {
	            $query .=  "AND o.rent=?";
	            $params[] = (int) $rent;
	        }
	
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
			
    	}
		return $list;
	}
        
    /**
	 * Returns a unique list of house constructions status (used in houses).
     * @param int $idLng
     * @param string $object
     * @param string $location
     * @param int $rent
	 * @return string[]
	 */
    public static function GetObjectTypesLocations($idLng = 1045, $object='', $location='', $rent=null) {
		
    	if(!($list = OffersHelper::getMethodResultCache())) {
	    	$db = DataBase::GetDbInstance();
	                
			$params = array((int) $idLng);
			$query = "SELECT DISTINCT(o.object_type) FROM #S#offers AS o WHERE o.id_lng=? ";
	                
	        if ($object != '' ) {
	            $query .=  "AND o.object=? ";
	            $params[] = $object;
	        }
	        if ($location != '' ) {
	            $query .=  "AND (o.location=? OR o.quarter=? OR o.region=?) ";
	            $params = array_merge($params, array($location, $location, $location));
	        }
	        if ($rent !== null ) {
	            $query .=  "AND o.rent=?";
	            $params[] = (int) $rent;
	        }
	
			$result = $db->ExecuteQueryWithParams($query, $params);
			$list = array();
			while($row = $db->FetchArray($result)){
				$list[count($list)] = $row[0];
			}
			
			OffersHelper::setMethodResultCache($list);
    	}
		return $list;
	}        
        
    /**
	 * Returns property objects from offers and count them.
	 * @param string $name
     * @param int $idLng
	 * @return properties array
	 */
	public static function GetPropertiesFromOffersCount($name, $idLng=1045){
        $db = DataBase::GetDbInstance();
        $params = array((int) OffersHelper::getProps($name), (int) $idLng);
        
        $query = "SELECT op.value, count(op.value) FROM #S#offers_properties op WHERE op.properties_id=? AND op.offers_id_lng=? GROUP BY op.value";
        
        $result = $db->ExecuteQueryWithParams($query, $params);
        $list = array();
        while($row = $db->FetchArray($result)){
            $list[$row[0]] = $row[1];
        }
        return $list;
	}
    
    /**
     * Verifies offers
     * @return int[]
     */
    public static function VerifyOffers() {
        
    	$list = array();
        $xml = new XMLReader();
        $domdoc = new DOMDocument;
        $xml->open(WebServiceVirgo::TMP_XML_OFELIST_FILE);
        
        Errors::LogSynchroStep('Offers - VerifyOffers() - step 1');        
        
        $xml->read();
        while($xml->name){	     
        	if($xml->name == 'Oferta'){   
	        	$node = simplexml_import_dom($domdoc->importNode($xml->expand(), true));
				$list[] = (int)$node["ID"];	                
        	}
        	$xml->read();
        }
        
        Errors::LogSynchroStep('Offers - VerifyOffers() - step 2');        
        
        $db = DataBase::GetDbInstance();
        $query = "SELECT DISTINCT(id) FROM #S#offers o";
        $result = $db->ExecuteQuery($query);

        $ofrs = new Offers();
        //delete wrong offers
        $localOffs = array();
        $flippedList = array_flip($list);
        while($row = $db->FetchArray($result)){
        	if(!isset($flippedList[$row[0]])){             
                 $ofrs->DeleteOffer($row[0]);
             }else {
                 $localOffs[] = $row[0];
             }
        }
        //detect missing offers
        $braki = array();        
        $flippedLocalOffs = array_flip($localOffs);
        foreach ($list as $id) {
            if (!isset($flippedLocalOffs[$id])) {
                $braki[] = $id;
            }
        }
        
        Errors::LogSynchroStep('Offers - VerifyOffers() - step 3');        
        return $braki;
    }

    /**
     * Gets offers id array and no. of visits
     * @return int[][]
     */
    public static function GetOffersViews(){
        $db = DataBase::GetDbInstance();
        $query = "SELECT DISTINCT(id), display_number FROM #S#offers WHERE display_number>0";
        $result = $db->ExecuteQuery($query);
        $lst = array();
        while($row = $db->FetchArray($result)){
            $lst[] = array($row[0], $row[1]);
        }
        $result = $db->ExecuteQuery("UPDATE #S#offers SET display_number=0");
        return $lst;
    }
    
    /**
     * Return unique list of used languages in offers.
     * @return Language[]
     */
    public static function GetAvailableLanguages(){
    	
    	if(!($lngs = OffersHelper::getMethodResultCache())) {
    		
	    	$result = DataBase::GetDbInstance()->ExecuteQuery("SELECT DISTINCT(id_lng) FROM #S#offers ORDER BY id_lng ASC");
	        $lngs = array();
			$ndx = 0;
			while($row = DataBase::GetDbInstance()->FetchArray($result)){
				$lngs[$ndx] = new Language($row[0]);
				$ndx++;
			}
			
			OffersHelper::setMethodResultCache($lngs);
    	}
		return $lngs;
    }
    
    /**
     * Return list of counted offers
     * @return array
     */
    public static function GetCountedOffers(){
        $db = DataBase::GetDbInstance();
        $counts = array();
        $query = "SELECT LCASE(province), rent, LCASE(object), COUNT(id) FROM offers GROUP BY province, rent, object";
        $result = DataBase::GetDbInstance()->ExecuteQuery($query);
        while($row = DataBase::GetDbInstance()->FetchArray($result)){
            $counts[$row[0]][$row[1]][$row[2]]=$row[3];
        }
        return $counts;
    }
}

?>