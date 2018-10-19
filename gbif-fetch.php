<?php

error_reporting(E_ALL);

require_once('couchsimple.php');

$force = true;

//----------------------------------------------------------------------------------------
function get($url)
{
	$data = null;
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------
// GBIF API
function get_occurrence($id)
{
	global $couch;

	$data = null;
	
	$url = 'https://api.gbif.org/v1/occurrence/' . $id;
	
	//echo $url . "\n";
	
	$json = get($url);
	
	//echo $json;
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if ($obj)
		{
			$data = new stdclass;
			
			$data->_id = 'https://gbif.org/occurrence/' . $id;
			
			$data->{'message-format'} = 'application/json';
			
			// Set URL we got data from
			$data->{'message-source'} = $url;
						
			$data->message = $obj;
			



			
		}
	}
	
	return $data;
}


//----------------------------------------------------------------------------------------
function gbif_fetch($id)
{
	global $couch;
	global $force;
	
	$_id = 'https://gbif.org/occurrence/' . $id;
	
	$exists = $couch->exists($_id);

	$go = true;
	if ($exists && !$force)
	{
		echo "Have already\n";
		$go = false;
	}

	if ($go)
	{
		$doc = get_occurrence($id);
				
		if ($doc)
		{
		
			if (!$exists)
			{
				$couch->add_update_or_delete_document($doc, $doc->_id, 'add');	
			}
			else
			{
				if ($force)
				{
					$couch->add_update_or_delete_document($doc, $doc->_id, 'update');
				}
			}
		}
	}	
}


// test cases
if (1)
{

	$force = true;
	$force = false;
	
	$ids=array(
	1066474657,
	
	691072021,
	
	1100252191, //   "taxonConceptID": "urn:lsid:biodiversity.org.au:afd.taxon:bd28ab20-363a-4760-b9dd-bd988292e3b4",

	1100318542,  // "eventID": "urn:australianmuseum.net.au:Events:1194967",
	
	1317230794, // USNM type with images
	
	1653484982, // type but not flagged as such in GBIF, and taxon match is to genus not species
	
	
	
	);
	
	$ids=array(
	1080490500 // EMBL Australia specimen accession FJ429866

	);
	
	foreach ($ids as $id)
	{
		gbif_fetch($id);
	}
}

// use API to search for specific records

if (0)
{
	$force = false;
	
	$species = array(
	//2295134
	//7990895
2295112,
8977766,
8065137,
8942633,
2295130,
9227302,
8391697,
7440913,
8066455,
7370634,
7749648,
8218828,
7558628,
9168603,
2295122,
7188284,
6181424,
2295129,
7990895,
2295117,
7629927,
2295121,
7631157,
2295115,
2295113,
7944584,
9129190,
8919094,
7676295,
2295140,
8141095,
2295154,
7485398,
8829086,
8863008,
8189593,
8264869,
7748329,
9070003,
9189500,
2295134,
8342421,
8390366,
2295128,
2295148,
7868236,
8018320,
8777157,
8312757,
7397892,
2295120,
9011255,
7822504,
2295141,
2295147,
2295135,
9033550,
8237033,
2295127,
2295114,
7795471,
8831063,
9191720,
9131373,
8940549,
9106542,
8921126,
8975660,
2295142,
7557372,
2295138,
7512721,	
	);

	// 
	foreach ($species as $taxonKey)
	{
		$url = 'https://api.gbif.org/v1/occurrence/search?taxonKey=' . $taxonKey;
	
		// images
		//$url .= '&mediaType=StillImage';
		
		// types
		$url .= '&typeStatus=*';
		
		
		//echo $url;
	
		$json = get($url);
		
		//echo $json;
		
		if ($json != '')
		{
			$obj = json_decode($json);
		
			$ids = array();
		
			if (isset($obj->results))
			{		
				foreach ($obj->results as $result)
				{
					$ids[] = $result->key;
				}
			}
			
			foreach ($ids as $id)
			{
				gbif_fetch($id);
			}			
			
		}
	}
}

// from file
/*
if (1)
{
	$force = false;

	$filename = 'doi.txt';
	
	$file_handle = fopen($filename, "r");
	while (!feof($file_handle)) 
	{
		$doi = trim(fgets($file_handle));
		
		crossref_fetch($doi);
	}
}
*/

?>
