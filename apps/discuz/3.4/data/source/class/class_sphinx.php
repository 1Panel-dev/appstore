<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_sphinx.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define( "SEARCHD_COMMAND_SEARCH",	0 );
define( "SEARCHD_COMMAND_EXCERPT",	1 );
define( "SEARCHD_COMMAND_UPDATE",	2 );
define( "SEARCHD_COMMAND_KEYWORDS",3 );
define( "SEARCHD_COMMAND_PERSIST",	4 );
define( "SEARCHD_COMMAND_STATUS",	5 );
define( "SEARCHD_COMMAND_QUERY",	6 );

define( "VER_COMMAND_SEARCH",		0x116 );
define( "VER_COMMAND_EXCERPT",		0x100 );
define( "VER_COMMAND_UPDATE",		0x102 );
define( "VER_COMMAND_KEYWORDS",	0x100 );
define( "VER_COMMAND_STATUS",		0x100 );
define( "VER_COMMAND_QUERY",		0x100 );

define( "SEARCHD_OK",				0 );
define( "SEARCHD_ERROR",			1 );
define( "SEARCHD_RETRY",			2 );
define( "SEARCHD_WARNING",			3 );

define( "SPH_MATCH_ALL",			0 );
define( "SPH_MATCH_ANY",			1 );
define( "SPH_MATCH_PHRASE",		2 );
define( "SPH_MATCH_BOOLEAN",		3 );
define( "SPH_MATCH_EXTENDED",		4 );
define( "SPH_MATCH_FULLSCAN",		5 );
define( "SPH_MATCH_EXTENDED2",		6 );	// extended engine V2 (TEMPORARY, WILL BE REMOVED)

define( "SPH_RANK_PROXIMITY_BM25",	0 );	///< default mode, phrase proximity major factor and BM25 minor one
define( "SPH_RANK_BM25",			1 );	///< statistical mode, BM25 ranking only (faster but worse quality)
define( "SPH_RANK_NONE",			2 );	///< no ranking, all matches get a weight of 1
define( "SPH_RANK_WORDCOUNT",		3 );	///< simple word-count weighting, rank is a weighted sum of per-field keyword occurence counts
define( "SPH_RANK_PROXIMITY",		4 );
define( "SPH_RANK_MATCHANY",		5 );
define( "SPH_RANK_FIELDMASK",		6 );

define( "SPH_SORT_RELEVANCE",		0 );
define( "SPH_SORT_ATTR_DESC",		1 );
define( "SPH_SORT_ATTR_ASC",		2 );
define( "SPH_SORT_TIME_SEGMENTS", 	3 );
define( "SPH_SORT_EXTENDED", 		4 );
define( "SPH_SORT_EXPR", 			5 );

define( "SPH_FILTER_VALUES",		0 );
define( "SPH_FILTER_RANGE",		1 );
define( "SPH_FILTER_FLOATRANGE",	2 );

define( "SPH_ATTR_INTEGER",		1 );
define( "SPH_ATTR_TIMESTAMP",		2 );
define( "SPH_ATTR_ORDINAL",		3 );
define( "SPH_ATTR_BOOL",			4 );
define( "SPH_ATTR_FLOAT",			5 );
define( "SPH_ATTR_BIGINT",			6 );
define( "SPH_ATTR_MULTI",			0x40000000 );

define( "SPH_GROUPBY_DAY",			0 );
define( "SPH_GROUPBY_WEEK",		1 );
define( "SPH_GROUPBY_MONTH",		2 );
define( "SPH_GROUPBY_YEAR",		3 );
define( "SPH_GROUPBY_ATTR",		4 );
define( "SPH_GROUPBY_ATTRPAIR",	5 );



function sphPackI64 ( $v )
{
	assert ( is_numeric($v) );

	if ( PHP_INT_SIZE>=8 )
	{
		$v = (int)$v;
		return pack ( "NN", $v>>32, $v&0xFFFFFFFF );
	}

	if ( is_int($v) )
		return pack ( "NN", $v < 0 ? -1 : 0, $v );

	if ( function_exists("bcmul") )
	{
		if ( bccomp ( $v, 0 ) == -1 )
			$v = bcadd ( "18446744073709551616", $v );
		$h = bcdiv ( $v, "4294967296", 0 );
		$l = bcmod ( $v, "4294967296" );
		return pack ( "NN", (float)$h, (float)$l ); // conversion to float is intentional; int would lose 31st bit
	}

	$p = max(0, strlen($v) - 13);
	$lo = abs((float)substr($v, $p));
	$hi = abs((float)substr($v, 0, $p));

	$m = $lo + $hi*1316134912.0; // (10 ^ 13) % (1 << 32) = 1316134912
	$q = floor($m/4294967296.0);
	$l = $m - ($q*4294967296.0);
	$h = $hi*2328.0 + $q; // (10 ^ 13) / (1 << 32) = 2328

	if ( $v<0 )
	{
		if ( $l==0 )
			$h = 4294967296.0 - $h;
		else
		{
			$h = 4294967295.0 - $h;
			$l = 4294967296.0 - $l;
		}
	}
	return pack ( "NN", $h, $l );
}

function sphPackU64 ( $v )
{
	assert ( is_numeric($v) );

	if ( PHP_INT_SIZE>=8 )
	{
		assert ( $v>=0 );

		if ( is_int($v) )
			return pack ( "NN", $v>>32, $v&0xFFFFFFFF );

		if ( function_exists("bcmul") )
		{
			$h = bcdiv ( $v, 4294967296, 0 );
			$l = bcmod ( $v, 4294967296 );
			return pack ( "NN", $h, $l );
		}

		$p = max ( 0, strlen($v) - 13 );
		$lo = (int)substr ( $v, $p );
		$hi = (int)substr ( $v, 0, $p );

		$m = $lo + $hi*1316134912;
		$l = $m % 4294967296;
		$h = $hi*2328 + (int)($m/4294967296);

		return pack ( "NN", $h, $l );
	}

	if ( is_int($v) )
		return pack ( "NN", 0, $v );

	if ( function_exists("bcmul") )
	{
		$h = bcdiv ( $v, "4294967296", 0 );
		$l = bcmod ( $v, "4294967296" );
		return pack ( "NN", (float)$h, (float)$l ); // conversion to float is intentional; int would lose 31st bit
	}

	$p = max(0, strlen($v) - 13);
	$lo = (float)substr($v, $p);
	$hi = (float)substr($v, 0, $p);

	$m = $lo + $hi*1316134912.0;
	$q = floor($m / 4294967296.0);
	$l = $m - ($q * 4294967296.0);
	$h = $hi*2328.0 + $q;

	return pack ( "NN", $h, $l );
}

function sphUnpackU64 ( $v )
{
	list ( $hi, $lo ) = array_values ( unpack ( "N*N*", $v ) );

	if ( PHP_INT_SIZE>=8 )
	{
		if ( $hi<0 ) $hi += (1<<32); // because php 5.2.2 to 5.2.5 is totally fucked up again
		if ( $lo<0 ) $lo += (1<<32);

		if ( $hi<=2147483647 )
			return ($hi<<32) + $lo;

		if ( function_exists("bcmul") )
			return bcadd ( $lo, bcmul ( $hi, "4294967296" ) );

		$C = 100000;
		$h = ((int)($hi / $C) << 32) + (int)($lo / $C);
		$l = (($hi % $C) << 32) + ($lo % $C);
		if ( $l>$C )
		{
			$h += (int)($l / $C);
			$l  = $l % $C;
		}

		if ( $h==0 )
			return $l;
		return sprintf ( "%d%05d", $h, $l );
	}

	if ( $hi==0 )
	{
		if ( $lo>0 )
			return $lo;
		return sprintf ( "%u", $lo );
	}

	$hi = sprintf ( "%u", $hi );
	$lo = sprintf ( "%u", $lo );

	if ( function_exists("bcmul") )
		return bcadd ( $lo, bcmul ( $hi, "4294967296" ) );

	$hi = (float)$hi;
	$lo = (float)$lo;

	$q = floor($hi/10000000.0);
	$r = $hi - $q*10000000.0;
	$m = $lo + $r*4967296.0;
	$mq = floor($m/10000000.0);
	$l = $m - $mq*10000000.0;
	$h = $q*4294967296.0 + $r*429.0 + $mq;

	$h = sprintf ( "%.0f", $h );
	$l = sprintf ( "%07.0f", $l );
	if ( $h=="0" )
		return sprintf( "%.0f", (float)$l );
	return $h . $l;
}

function sphUnpackI64 ( $v )
{
	list ( $hi, $lo ) = array_values ( unpack ( "N*N*", $v ) );

	if ( PHP_INT_SIZE>=8 )
	{
		if ( $hi<0 ) $hi += (1<<32); // because php 5.2.2 to 5.2.5 is totally fucked up again
		if ( $lo<0 ) $lo += (1<<32);

		return ($hi<<32) + $lo;
	}

	if ( $hi==0 )
	{
		if ( $lo>0 )
			return $lo;
		return sprintf ( "%u", $lo );
	}
	elseif ( $hi==-1 )
	{
		if ( $lo<0 )
			return $lo;
		return sprintf ( "%.0f", $lo - 4294967296.0 );
	}

	$neg = "";
	$c = 0;
	if ( $hi<0 )
	{
		$hi = ~$hi;
		$lo = ~$lo;
		$c = 1;
		$neg = "-";
	}

	$hi = sprintf ( "%u", $hi );
	$lo = sprintf ( "%u", $lo );

	if ( function_exists("bcmul") )
		return $neg . bcadd ( bcadd ( $lo, bcmul ( $hi, "4294967296" ) ), $c );

	$hi = (float)$hi;
	$lo = (float)$lo;

	$q = floor($hi/10000000.0);
	$r = $hi - $q*10000000.0;
	$m = $lo + $r*4967296.0;
	$mq = floor($m/10000000.0);
	$l = $m - $mq*10000000.0 + $c;
	$h = $q*4294967296.0 + $r*429.0 + $mq;
	if ( $l==10000000 )
	{
		$l = 0;
		$h += 1;
	}

	$h = sprintf ( "%.0f", $h );
	$l = sprintf ( "%07.0f", $l );
	if ( $h=="0" )
		return $neg . sprintf( "%.0f", (float)$l );
	return $neg . $h . $l;
}


function sphFixUint ( $value )
{
	if ( PHP_INT_SIZE>=8 )
	{
		if ( $value<0 ) $value += (1<<32);
		return $value;
	}
	else
	{
		return sprintf ( "%u", $value );
	}
}


class SphinxClient
{
	var $_host;			///< searchd host (default is "localhost")
	var $_port;			///< searchd port (default is 9312)
	var $_offset;		///< how many records to seek from result-set start (default is 0)
	var $_limit;		///< how many records to return from result-set starting at offset (default is 20)
	var $_mode;			///< query matching mode (default is SPH_MATCH_ALL)
	var $_weights;		///< per-field weights (default is 1 for all fields)
	var $_sort;			///< match sorting mode (default is SPH_SORT_RELEVANCE)
	var $_sortby;		///< attribute to sort by (defualt is "")
	var $_min_id;		///< min ID to match (default is 0, which means no limit)
	var $_max_id;		///< max ID to match (default is 0, which means no limit)
	var $_filters;		///< search filters
	var $_groupby;		///< group-by attribute name
	var $_groupfunc;	///< group-by function (to pre-process group-by attribute value with)
	var $_groupsort;	///< group-by sorting clause (to sort groups in result set with)
	var $_groupdistinct;///< group-by count-distinct attribute
	var $_maxmatches;	///< max matches to retrieve
	var $_cutoff;		///< cutoff to stop searching at (default is 0)
	var $_retrycount;	///< distributed retries count
	var $_retrydelay;	///< distributed retries delay
	var $_anchor;		///< geographical anchor point
	var $_indexweights;	///< per-index weights
	var $_ranker;		///< ranking mode (default is SPH_RANK_PROXIMITY_BM25)
	var $_maxquerytime;	///< max query time, milliseconds (default is 0, do not limit)
	var $_fieldweights;	///< per-field-name weights
	var $_overrides;	///< per-query attribute values overrides
	var $_select;		///< select-list (attributes or expressions, with optional aliases)

	var $_error;		///< last error message
	var $_warning;		///< last warning message
	var $_connerror;		///< connection error vs remote error flag

	var $_reqs;			///< requests array for multi-query
	var $_mbenc;		///< stored mbstring encoding
	var $_arrayresult;	///< whether $result["matches"] should be a hash or an array
	var $_timeout;		///< connect timeout


	function SphinxClient ()
	{
		$this->_host		= "localhost";
		$this->_port		= 9312;
		$this->_path		= false;
		$this->_socket		= false;

		$this->_offset		= 0;
		$this->_limit		= 20;
		$this->_mode		= SPH_MATCH_ALL;
		$this->_weights		= array ();
		$this->_sort		= SPH_SORT_RELEVANCE;
		$this->_sortby		= "";
		$this->_min_id		= 0;
		$this->_max_id		= 0;
		$this->_filters		= array ();
		$this->_groupby		= "";
		$this->_groupfunc	= SPH_GROUPBY_DAY;
		$this->_groupsort	= "@group desc";
		$this->_groupdistinct= "";
		$this->_maxmatches	= 1000;
		$this->_cutoff		= 0;
		$this->_retrycount	= 0;
		$this->_retrydelay	= 0;
		$this->_anchor		= array ();
		$this->_indexweights= array ();
		$this->_ranker		= SPH_RANK_PROXIMITY_BM25;
		$this->_maxquerytime= 0;
		$this->_fieldweights= array();
		$this->_overrides 	= array();
		$this->_select		= "*";

		$this->_error		= ""; // per-reply fields (for single-query case)
		$this->_warning		= "";
		$this->_connerror	= false;

		$this->_reqs		= array ();	// requests storage (for multi-query case)
		$this->_mbenc		= "";
		$this->_arrayresult	= false;
		$this->_timeout		= 0;
	}

	function __destruct()
	{
		if ( $this->_socket !== false )
			fclose ( $this->_socket );
	}

	function GetLastError ()
	{
		return $this->_error;
	}

	function GetLastWarning ()
	{
		return $this->_warning;
	}

	function IsConnectError()
	{
		return $this->_connerror;
	}

	function SetServer ( $host, $port = 0 )
	{
		assert ( is_string($host) );
		if ( $host[0] == '/')
		{
			$this->_path = 'unix://' . $host;
			return;
		}
		if ( substr ( $host, 0, 7 )=="unix://" )
		{
			$this->_path = $host;
			return;
		}

		assert ( is_int($port) );
		$this->_host = $host;
		$this->_port = $port;
		$this->_path = '';

	}

	function SetConnectTimeout ( $timeout )
	{
		assert ( is_numeric($timeout) );
		$this->_timeout = $timeout;
	}


	function _Send ( $handle, $data, $length )
	{
		if ( feof($handle) || fwrite ( $handle, $data, $length ) !== $length )
		{
			$this->_error = 'connection unexpectedly closed (timed out?)';
			$this->_connerror = true;
			return false;
		}
		return true;
	}


	function _MBPush ()
	{
		$this->_mbenc = "";
		if ( ini_get ( "mbstring.func_overload" ) & 2 )
		{
			$this->_mbenc = mb_internal_encoding();
			mb_internal_encoding ( "latin1" );
		}
    }

	function _MBPop ()
	{
		if ( $this->_mbenc )
			mb_internal_encoding ( $this->_mbenc );
	}

	function _Connect ()
	{
		if ( $this->_socket!==false )
		{
			if ( !@feof ( $this->_socket ) )
				return $this->_socket;

			$this->_socket = false;
		}

		$errno = 0;
		$errstr = "";
		$this->_connerror = false;

		if ( $this->_path )
		{
			$host = $this->_path;
			$port = 0;
		}
		else
		{
			$host = $this->_host;
			$port = $this->_port;
		}

		if ( $this->_timeout<=0 )
			$fp = fsocketopen ( $host, $port, $errno, $errstr );
		else
			$fp = fsocketopen ( $host, $port, $errno, $errstr, $this->_timeout );

		if ( !$fp )
		{
			if ( $this->_path )
				$location = $this->_path;
			else
				$location = "{$this->_host}:{$this->_port}";

			$errstr = trim ( $errstr );
			$this->_error = "connection to $location failed (errno=$errno, msg=$errstr)";
			$this->_connerror = true;
			return false;
		}

		if ( !$this->_Send ( $fp, pack ( "N", 1 ), 4 ) )
		{
			fclose ( $fp );
			$this->_error = "failed to send client protocol version";
			return false;
		}

		list(,$v) = unpack ( "N*", fread ( $fp, 4 ) );
		$v = (int)$v;
		if ( $v<1 )
		{
			fclose ( $fp );
			$this->_error = "expected searchd protocol version 1+, got version '$v'";
			return false;
		}

		return $fp;
	}

	function _GetResponse ( $fp, $client_ver )
	{
		$response = "";
		$len = 0;

		$header = fread ( $fp, 8 );
		if ( strlen($header)==8 )
		{
			list ( $status, $ver, $len ) = array_values ( unpack ( "n2a/Nb", $header ) );
			$left = $len;
			while ( $left>0 && !feof($fp) )
			{
				$chunk = fread ( $fp, $left );
				if ( $chunk )
				{
					$response .= $chunk;
					$left -= strlen($chunk);
				}
			}
		}
		if ( $this->_socket === false )
			fclose ( $fp );

		$read = strlen ( $response );
		if ( !$response || $read!=$len )
		{
			$this->_error = $len
				? "failed to read searchd response (status=$status, ver=$ver, len=$len, read=$read)"
				: "received zero-sized searchd response";
			return false;
		}

		if ( $status==SEARCHD_WARNING )
		{
			list(,$wlen) = unpack ( "N*", substr ( $response, 0, 4 ) );
			$this->_warning = substr ( $response, 4, $wlen );
			return substr ( $response, 4+$wlen );
		}
		if ( $status==SEARCHD_ERROR )
		{
			$this->_error = "searchd error: " . substr ( $response, 4 );
			return false;
		}
		if ( $status==SEARCHD_RETRY )
		{
			$this->_error = "temporary searchd error: " . substr ( $response, 4 );
			return false;
		}
		if ( $status!=SEARCHD_OK )
		{
			$this->_error = "unknown status code '$status'";
			return false;
		}

		if ( $ver<$client_ver )
		{
			$this->_warning = sprintf ( "searchd command v.%d.%d older than client's v.%d.%d, some options might not work",
				$ver>>8, $ver&0xff, $client_ver>>8, $client_ver&0xff );
		}

		return $response;
	}


	function SetLimits ( $offset, $limit, $max=0, $cutoff=0 )
	{
		assert ( is_int($offset) );
		assert ( is_int($limit) );
		assert ( $offset>=0 );
		assert ( $limit>0 );
		assert ( $max>=0 );
		$this->_offset = $offset;
		$this->_limit = $limit;
		if ( $max>0 )
			$this->_maxmatches = $max;
		if ( $cutoff>0 )
			$this->_cutoff = $cutoff;
	}

	function SetMaxQueryTime ( $max )
	{
		assert ( is_int($max) );
		assert ( $max>=0 );
		$this->_maxquerytime = $max;
	}

	function SetMatchMode ( $mode )
	{
		assert ( $mode==SPH_MATCH_ALL
			|| $mode==SPH_MATCH_ANY
			|| $mode==SPH_MATCH_PHRASE
			|| $mode==SPH_MATCH_BOOLEAN
			|| $mode==SPH_MATCH_EXTENDED
			|| $mode==SPH_MATCH_FULLSCAN
			|| $mode==SPH_MATCH_EXTENDED2 );
		$this->_mode = $mode;
	}

	function SetRankingMode ( $ranker )
	{
		assert ( $ranker==SPH_RANK_PROXIMITY_BM25
			|| $ranker==SPH_RANK_BM25
			|| $ranker==SPH_RANK_NONE
			|| $ranker==SPH_RANK_WORDCOUNT
			|| $ranker==SPH_RANK_PROXIMITY );
		$this->_ranker = $ranker;
	}

	function SetSortMode ( $mode, $sortby="" )
	{
		assert (
			$mode==SPH_SORT_RELEVANCE ||
			$mode==SPH_SORT_ATTR_DESC ||
			$mode==SPH_SORT_ATTR_ASC ||
			$mode==SPH_SORT_TIME_SEGMENTS ||
			$mode==SPH_SORT_EXTENDED ||
			$mode==SPH_SORT_EXPR );
		assert ( is_string($sortby) );
		assert ( $mode==SPH_SORT_RELEVANCE || strlen($sortby)>0 );

		$this->_sort = $mode;
		$this->_sortby = $sortby;
	}

	function SetWeights ( $weights )
	{
		assert ( is_array($weights) );
		foreach ( $weights as $weight )
			assert ( is_int($weight) );

		$this->_weights = $weights;
	}

	function SetFieldWeights ( $weights )
	{
		assert ( is_array($weights) );
		foreach ( $weights as $name=>$weight )
		{
			assert ( is_string($name) );
			assert ( is_int($weight) );
		}
		$this->_fieldweights = $weights;
	}

	function SetIndexWeights ( $weights )
	{
		assert ( is_array($weights) );
		foreach ( $weights as $index=>$weight )
		{
			assert ( is_string($index) );
			assert ( is_int($weight) );
		}
		$this->_indexweights = $weights;
	}

	function SetIDRange ( $min, $max )
	{
		assert ( is_numeric($min) );
		assert ( is_numeric($max) );
		assert ( $min<=$max );
		$this->_min_id = $min;
		$this->_max_id = $max;
	}

	function SetFilter ( $attribute, $values, $exclude=false )
	{
		assert ( is_string($attribute) );
		assert ( is_array($values) );
		assert ( count($values) );

		if ( is_array($values) && count($values) )
		{
			foreach ( $values as $value )
				assert ( is_numeric($value) );

			$this->_filters[] = array ( "type"=>SPH_FILTER_VALUES, "attr"=>$attribute, "exclude"=>$exclude, "values"=>$values );
		}
	}

	function SetFilterRange ( $attribute, $min, $max, $exclude=false )
	{
		assert ( is_string($attribute) );
		assert ( is_numeric($min) );
		assert ( is_numeric($max) );
		assert ( $min<=$max );

		$this->_filters[] = array ( "type"=>SPH_FILTER_RANGE, "attr"=>$attribute, "exclude"=>$exclude, "min"=>$min, "max"=>$max );
	}

	function SetFilterFloatRange ( $attribute, $min, $max, $exclude=false )
	{
		assert ( is_string($attribute) );
		assert ( is_float($min) );
		assert ( is_float($max) );
		assert ( $min<=$max );

		$this->_filters[] = array ( "type"=>SPH_FILTER_FLOATRANGE, "attr"=>$attribute, "exclude"=>$exclude, "min"=>$min, "max"=>$max );
	}

	function SetGeoAnchor ( $attrlat, $attrlong, $lat, $long )
	{
		assert ( is_string($attrlat) );
		assert ( is_string($attrlong) );
		assert ( is_float($lat) );
		assert ( is_float($long) );

		$this->_anchor = array ( "attrlat"=>$attrlat, "attrlong"=>$attrlong, "lat"=>$lat, "long"=>$long );
	}

	function SetGroupBy ( $attribute, $func, $groupsort="@group desc" )
	{
		assert ( is_string($attribute) );
		assert ( is_string($groupsort) );
		assert ( $func==SPH_GROUPBY_DAY
			|| $func==SPH_GROUPBY_WEEK
			|| $func==SPH_GROUPBY_MONTH
			|| $func==SPH_GROUPBY_YEAR
			|| $func==SPH_GROUPBY_ATTR
			|| $func==SPH_GROUPBY_ATTRPAIR );

		$this->_groupby = $attribute;
		$this->_groupfunc = $func;
		$this->_groupsort = $groupsort;
	}

	function SetGroupDistinct ( $attribute )
	{
		assert ( is_string($attribute) );
		$this->_groupdistinct = $attribute;
	}

	function SetRetries ( $count, $delay=0 )
	{
		assert ( is_int($count) && $count>=0 );
		assert ( is_int($delay) && $delay>=0 );
		$this->_retrycount = $count;
		$this->_retrydelay = $delay;
	}

	function SetArrayResult ( $arrayresult )
	{
		assert ( is_bool($arrayresult) );
		$this->_arrayresult = $arrayresult;
	}

	function SetOverride ( $attrname, $attrtype, $values )
	{
		assert ( is_string ( $attrname ) );
		assert ( in_array ( $attrtype, array ( SPH_ATTR_INTEGER, SPH_ATTR_TIMESTAMP, SPH_ATTR_BOOL, SPH_ATTR_FLOAT, SPH_ATTR_BIGINT ) ) );
		assert ( is_array ( $values ) );

		$this->_overrides[$attrname] = array ( "attr"=>$attrname, "type"=>$attrtype, "values"=>$values );
	}

	function SetSelect ( $select )
	{
		assert ( is_string ( $select ) );
		$this->_select = $select;
	}


	function ResetFilters ()
	{
		$this->_filters = array();
		$this->_anchor = array();
	}

	function ResetGroupBy ()
	{
		$this->_groupby		= "";
		$this->_groupfunc	= SPH_GROUPBY_DAY;
		$this->_groupsort	= "@group desc";
		$this->_groupdistinct= "";
	}

	function ResetOverrides ()
    {
    	$this->_overrides = array ();
    }


	function Query ( $query, $index="*", $comment="" )
	{
		assert ( empty($this->_reqs) );

		$this->AddQuery ( $query, $index, $comment );

		$results = $this->RunQueries ();

		$this->_reqs = array (); // just in case it failed too early

		if ( !is_array($results) )
			return false; // probably network error; error message should be already filled

		$this->_error = $results[0]["error"];
		$this->_warning = $results[0]["warning"];
		if ( $results[0]["status"]==SEARCHD_ERROR )
			return false;
		else
			return $results[0];
	}

	function _PackFloat ( $f )
	{
		$t1 = pack ( "f", $f ); // machine order
		list(,$t2) = unpack ( "L*", $t1 ); // int in machine order
		return pack ( "N", $t2 );
	}

	function AddQuery ( $query, $index="*", $comment="" )
	{
		$this->_MBPush ();

		$req = pack ( "NNNNN", $this->_offset, $this->_limit, $this->_mode, $this->_ranker, $this->_sort ); // mode and limits
		$req .= pack ( "N", strlen($this->_sortby) ) . $this->_sortby;
		$req .= pack ( "N", strlen($query) ) . $query; // query itself
		$req .= pack ( "N", count($this->_weights) ); // weights
		foreach ( $this->_weights as $weight )
			$req .= pack ( "N", (int)$weight );
		$req .= pack ( "N", strlen($index) ) . $index; // indexes
		$req .= pack ( "N", 1 ); // id64 range marker
		$req .= sphPackU64 ( $this->_min_id ) . sphPackU64 ( $this->_max_id ); // id64 range

		$req .= pack ( "N", count($this->_filters) );
		foreach ( $this->_filters as $filter )
		{
			$req .= pack ( "N", strlen($filter["attr"]) ) . $filter["attr"];
			$req .= pack ( "N", $filter["type"] );
			switch ( $filter["type"] )
			{
				case SPH_FILTER_VALUES:
					$req .= pack ( "N", count($filter["values"]) );
					foreach ( $filter["values"] as $value )
						$req .= sphPackI64 ( $value );
					break;

				case SPH_FILTER_RANGE:
					$req .= sphPackI64 ( $filter["min"] ) . sphPackI64 ( $filter["max"] );
					break;

				case SPH_FILTER_FLOATRANGE:
					$req .= $this->_PackFloat ( $filter["min"] ) . $this->_PackFloat ( $filter["max"] );
					break;

				default:
					assert ( 0 && "internal error: unhandled filter type" );
			}
			$req .= pack ( "N", $filter["exclude"] );
		}

		$req .= pack ( "NN", $this->_groupfunc, strlen($this->_groupby) ) . $this->_groupby;
		$req .= pack ( "N", $this->_maxmatches );
		$req .= pack ( "N", strlen($this->_groupsort) ) . $this->_groupsort;
		$req .= pack ( "NNN", $this->_cutoff, $this->_retrycount, $this->_retrydelay );
		$req .= pack ( "N", strlen($this->_groupdistinct) ) . $this->_groupdistinct;

		if ( empty($this->_anchor) )
		{
			$req .= pack ( "N", 0 );
		} else
		{
			$a =& $this->_anchor;
			$req .= pack ( "N", 1 );
			$req .= pack ( "N", strlen($a["attrlat"]) ) . $a["attrlat"];
			$req .= pack ( "N", strlen($a["attrlong"]) ) . $a["attrlong"];
			$req .= $this->_PackFloat ( $a["lat"] ) . $this->_PackFloat ( $a["long"] );
		}

		$req .= pack ( "N", count($this->_indexweights) );
		foreach ( $this->_indexweights as $idx=>$weight )
			$req .= pack ( "N", strlen($idx) ) . $idx . pack ( "N", $weight );

		$req .= pack ( "N", $this->_maxquerytime );

		$req .= pack ( "N", count($this->_fieldweights) );
		foreach ( $this->_fieldweights as $field=>$weight )
			$req .= pack ( "N", strlen($field) ) . $field . pack ( "N", $weight );

		$req .= pack ( "N", strlen($comment) ) . $comment;

		$req .= pack ( "N", count($this->_overrides) );
		foreach ( $this->_overrides as $key => $entry )
		{
			$req .= pack ( "N", strlen($entry["attr"]) ) . $entry["attr"];
			$req .= pack ( "NN", $entry["type"], count($entry["values"]) );
			foreach ( $entry["values"] as $id=>$val )
			{
				assert ( is_numeric($id) );
				assert ( is_numeric($val) );

				$req .= sphPackU64 ( $id );
				switch ( $entry["type"] )
				{
					case SPH_ATTR_FLOAT:	$req .= $this->_PackFloat ( $val ); break;
					case SPH_ATTR_BIGINT:	$req .= sphPackI64 ( $val ); break;
					default:				$req .= pack ( "N", $val ); break;
				}
			}
		}

		$req .= pack ( "N", strlen($this->_select) ) . $this->_select;

		$this->_MBPop ();

		$this->_reqs[] = $req;
		return count($this->_reqs)-1;
	}

	function RunQueries ()
	{

		if ( empty($this->_reqs) )
		{
			$this->_error = "no queries defined, issue AddQuery() first";
			return false;
		}

		$this->_MBPush ();

		if (!( $fp = $this->_Connect() ))
		{
			$this->_MBPop ();
			return false;
		}

		$nreqs = count($this->_reqs);
		$req = join ( "", $this->_reqs );
		$len = 4+strlen($req);
		$req = pack ( "nnNN", SEARCHD_COMMAND_SEARCH, VER_COMMAND_SEARCH, $len, $nreqs ) . $req; // add header
		if ( !( $this->_Send ( $fp, $req, $len+8 ) ) ||
			 !( $response = $this->_GetResponse ( $fp, VER_COMMAND_SEARCH ) ) )
		{
			$this->_MBPop ();
			return false;
		}

		$this->_reqs = array ();

		return $this->_ParseSearchResponse ( $response, $nreqs );
	}

	function _ParseSearchResponse ( $response, $nreqs )
	{
		$p = 0; // current position
		$max = strlen($response); // max position for checks, to protect against broken responses

		$results = array ();
		for ( $ires=0; $ires<$nreqs && $p<$max; $ires++ )
		{
			$results[] = array();
			$result =& $results[$ires];

			$result["error"] = "";
			$result["warning"] = "";

			list(,$status) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
			$result["status"] = $status;
			if ( $status!=SEARCHD_OK )
			{
				list(,$len) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
				$message = substr ( $response, $p, $len ); $p += $len;

				if ( $status==SEARCHD_WARNING )
				{
					$result["warning"] = $message;
				} else
				{
					$result["error"] = $message;
					continue;
				}
			}

			$fields = array ();
			$attrs = array ();

			list(,$nfields) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
			while ( $nfields-->0 && $p<$max )
			{
				list(,$len) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
				$fields[] = substr ( $response, $p, $len ); $p += $len;
			}
			$result["fields"] = $fields;

			list(,$nattrs) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
			while ( $nattrs-->0 && $p<$max  )
			{
				list(,$len) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
				$attr = substr ( $response, $p, $len ); $p += $len;
				list(,$type) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
				$attrs[$attr] = $type;
			}
			$result["attrs"] = $attrs;

			list(,$count) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
			list(,$id64) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;

			$idx = -1;
			while ( $count-->0 && $p<$max )
			{
				$idx++;

				if ( $id64 )
				{
					$doc = sphUnpackU64 ( substr ( $response, $p, 8 ) ); $p += 8;
					list(,$weight) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
				}
				else
				{
					list ( $doc, $weight ) = array_values ( unpack ( "N*N*",
						substr ( $response, $p, 8 ) ) );
					$p += 8;
					$doc = sphFixUint($doc);
				}
				$weight = sprintf ( "%u", $weight );

				if ( $this->_arrayresult )
					$result["matches"][$idx] = array ( "id"=>$doc, "weight"=>$weight );
				else
					$result["matches"][$doc]["weight"] = $weight;

				$attrvals = array ();
				foreach ( $attrs as $attr=>$type )
				{
					if ( $type==SPH_ATTR_BIGINT )
					{
						$attrvals[$attr] = sphUnpackI64 ( substr ( $response, $p, 8 ) ); $p += 8;
						continue;
					}

					if ( $type==SPH_ATTR_FLOAT )
					{
						list(,$uval) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
						list(,$fval) = unpack ( "f*", pack ( "L", $uval ) );
						$attrvals[$attr] = $fval;
						continue;
					}

					list(,$val) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
					if ( $type & SPH_ATTR_MULTI )
					{
						$attrvals[$attr] = array ();
						$nvalues = $val;
						while ( $nvalues-->0 && $p<$max )
						{
							list(,$val) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
							$attrvals[$attr][] = sphFixUint($val);
						}
					} else
					{
						$attrvals[$attr] = sphFixUint($val);
					}
				}

				if ( $this->_arrayresult )
					$result["matches"][$idx]["attrs"] = $attrvals;
				else
					$result["matches"][$doc]["attrs"] = $attrvals;
			}

			list ( $total, $total_found, $msecs, $words ) =
				array_values ( unpack ( "N*N*N*N*", substr ( $response, $p, 16 ) ) );
			$result["total"] = sprintf ( "%u", $total );
			$result["total_found"] = sprintf ( "%u", $total_found );
			$result["time"] = sprintf ( "%.3f", $msecs/1000 );
			$p += 16;

			while ( $words-->0 && $p<$max )
			{
				list(,$len) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
				$word = substr ( $response, $p, $len ); $p += $len;
				list ( $docs, $hits ) = array_values ( unpack ( "N*N*", substr ( $response, $p, 8 ) ) ); $p += 8;
				$result["words"][$word] = array (
					"docs"=>sprintf ( "%u", $docs ),
					"hits"=>sprintf ( "%u", $hits ) );
			}
		}

		$this->_MBPop ();
		return $results;
	}


	function BuildExcerpts ( $docs, $index, $words, $opts=array() )
	{
		assert ( is_array($docs) );
		assert ( is_string($index) );
		assert ( is_string($words) );
		assert ( is_array($opts) );

		$this->_MBPush ();

		if (!( $fp = $this->_Connect() ))
		{
			$this->_MBPop();
			return false;
		}


		if ( !isset($opts["before_match"]) )		$opts["before_match"] = "<b>";
		if ( !isset($opts["after_match"]) )			$opts["after_match"] = "</b>";
		if ( !isset($opts["chunk_separator"]) )		$opts["chunk_separator"] = " ... ";
		if ( !isset($opts["limit"]) )				$opts["limit"] = 256;
		if ( !isset($opts["around"]) )				$opts["around"] = 5;
		if ( !isset($opts["exact_phrase"]) )		$opts["exact_phrase"] = false;
		if ( !isset($opts["single_passage"]) )		$opts["single_passage"] = false;
		if ( !isset($opts["use_boundaries"]) )		$opts["use_boundaries"] = false;
		if ( !isset($opts["weight_order"]) )		$opts["weight_order"] = false;


		$flags = 1; // remove spaces
		if ( $opts["exact_phrase"] )	$flags |= 2;
		if ( $opts["single_passage"] )	$flags |= 4;
		if ( $opts["use_boundaries"] )	$flags |= 8;
		if ( $opts["weight_order"] )	$flags |= 16;
		$req = pack ( "NN", 0, $flags ); // mode=0, flags=$flags
		$req .= pack ( "N", strlen($index) ) . $index; // req index
		$req .= pack ( "N", strlen($words) ) . $words; // req words

		$req .= pack ( "N", strlen($opts["before_match"]) ) . $opts["before_match"];
		$req .= pack ( "N", strlen($opts["after_match"]) ) . $opts["after_match"];
		$req .= pack ( "N", strlen($opts["chunk_separator"]) ) . $opts["chunk_separator"];
		$req .= pack ( "N", (int)$opts["limit"] );
		$req .= pack ( "N", (int)$opts["around"] );

		$req .= pack ( "N", count($docs) );
		foreach ( $docs as $doc )
		{
			assert ( is_string($doc) );
			$req .= pack ( "N", strlen($doc) ) . $doc;
		}


		$len = strlen($req);
		$req = pack ( "nnN", SEARCHD_COMMAND_EXCERPT, VER_COMMAND_EXCERPT, $len ) . $req; // add header
		if ( !( $this->_Send ( $fp, $req, $len+8 ) ) ||
			 !( $response = $this->_GetResponse ( $fp, VER_COMMAND_EXCERPT ) ) )
		{
			$this->_MBPop ();
			return false;
		}


		$pos = 0;
		$res = array ();
		$rlen = strlen($response);
		for ( $i=0; $i<count($docs); $i++ )
		{
			list(,$len) = unpack ( "N*", substr ( $response, $pos, 4 ) );
			$pos += 4;

			if ( $pos+$len > $rlen )
			{
				$this->_error = "incomplete reply";
				$this->_MBPop ();
				return false;
			}
			$res[] = $len ? substr ( $response, $pos, $len ) : "";
			$pos += $len;
		}

		$this->_MBPop ();
		return $res;
	}



	function BuildKeywords ( $query, $index, $hits )
	{
		assert ( is_string($query) );
		assert ( is_string($index) );
		assert ( is_bool($hits) );

		$this->_MBPush ();

		if (!( $fp = $this->_Connect() ))
		{
			$this->_MBPop();
			return false;
		}


		$req  = pack ( "N", strlen($query) ) . $query; // req query
		$req .= pack ( "N", strlen($index) ) . $index; // req index
		$req .= pack ( "N", (int)$hits );


		$len = strlen($req);
		$req = pack ( "nnN", SEARCHD_COMMAND_KEYWORDS, VER_COMMAND_KEYWORDS, $len ) . $req; // add header
		if ( !( $this->_Send ( $fp, $req, $len+8 ) ) ||
			 !( $response = $this->_GetResponse ( $fp, VER_COMMAND_KEYWORDS ) ) )
		{
			$this->_MBPop ();
			return false;
		}


		$pos = 0;
		$res = array ();
		$rlen = strlen($response);
		list(,$nwords) = unpack ( "N*", substr ( $response, $pos, 4 ) );
		$pos += 4;
		for ( $i=0; $i<$nwords; $i++ )
		{
			list(,$len) = unpack ( "N*", substr ( $response, $pos, 4 ) );	$pos += 4;
			$tokenized = $len ? substr ( $response, $pos, $len ) : "";
			$pos += $len;

			list(,$len) = unpack ( "N*", substr ( $response, $pos, 4 ) );	$pos += 4;
			$normalized = $len ? substr ( $response, $pos, $len ) : "";
			$pos += $len;

			$res[] = array ( "tokenized"=>$tokenized, "normalized"=>$normalized );

			if ( $hits )
			{
				list($ndocs,$nhits) = array_values ( unpack ( "N*N*", substr ( $response, $pos, 8 ) ) );
				$pos += 8;
				$res [$i]["docs"] = $ndocs;
				$res [$i]["hits"] = $nhits;
			}

			if ( $pos > $rlen )
			{
				$this->_error = "incomplete reply";
				$this->_MBPop ();
				return false;
			}
		}

		$this->_MBPop ();
		return $res;
	}

	function EscapeString ( $string )
	{
		$from = array ( '\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=' );
		$to   = array ( '\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=' );

		return str_replace ( $from, $to, $string );
	}


	function UpdateAttributes ( $index, $attrs, $values, $mva=false )
	{
		assert ( is_string($index) );
		assert ( is_bool($mva) );

		assert ( is_array($attrs) );
		foreach ( $attrs as $attr )
			assert ( is_string($attr) );

		assert ( is_array($values) );
		foreach ( $values as $id=>$entry )
		{
			assert ( is_numeric($id) );
			assert ( is_array($entry) );
			assert ( count($entry)==count($attrs) );
			foreach ( $entry as $v )
			{
				if ( $mva )
				{
					assert ( is_array($v) );
					foreach ( $v as $vv )
						assert ( is_int($vv) );
				} else
					assert ( is_int($v) );
			}
		}

		$req = pack ( "N", strlen($index) ) . $index;

		$req .= pack ( "N", count($attrs) );
		foreach ( $attrs as $attr )
		{
			$req .= pack ( "N", strlen($attr) ) . $attr;
			$req .= pack ( "N", $mva ? 1 : 0 );
		}

		$req .= pack ( "N", count($values) );
		foreach ( $values as $id=>$entry )
		{
			$req .= sphPackU64 ( $id );
			foreach ( $entry as $v )
			{
				$req .= pack ( "N", $mva ? count($v) : $v );
				if ( $mva )
					foreach ( $v as $vv )
						$req .= pack ( "N", $vv );
			}
		}

		if (!( $fp = $this->_Connect() ))
			return -1;

		$len = strlen($req);
		$req = pack ( "nnN", SEARCHD_COMMAND_UPDATE, VER_COMMAND_UPDATE, $len ) . $req; // add header
		if ( !$this->_Send ( $fp, $req, $len+8 ) )
			return -1;

		if (!( $response = $this->_GetResponse ( $fp, VER_COMMAND_UPDATE ) ))
			return -1;

		list(,$updated) = unpack ( "N*", substr ( $response, 0, 4 ) );
		return $updated;
	}


	function Open()
	{
		if ( $this->_socket !== false )
		{
			$this->_error = 'already connected';
			return false;
		}
		if ( !$fp = $this->_Connect() )
			return false;

		$req = pack ( "nnNN", SEARCHD_COMMAND_PERSIST, 0, 4, 1 );
		if ( !$this->_Send ( $fp, $req, 12 ) )
			return false;

		$this->_socket = $fp;
		return true;
	}

	function Close()
	{
		if ( $this->_socket === false )
		{
			$this->_error = 'not connected';
			return false;
		}

		fclose ( $this->_socket );
		$this->_socket = false;

		return true;
	}


	function Status ()
	{
		$this->_MBPush ();
		if (!( $fp = $this->_Connect() ))
		{
			$this->_MBPop();
			return false;
		}

		$req = pack ( "nnNN", SEARCHD_COMMAND_STATUS, VER_COMMAND_STATUS, 4, 1 ); // len=4, body=1
		if ( !( $this->_Send ( $fp, $req, 12 ) ) ||
			 !( $response = $this->_GetResponse ( $fp, VER_COMMAND_STATUS ) ) )
		{
			$this->_MBPop ();
			return false;
		}

		$res = substr ( $response, 4 ); // just ignore length, error handling, etc
		$p = 0;
		list ( $rows, $cols ) = array_values ( unpack ( "N*N*", substr ( $response, $p, 8 ) ) ); $p += 8;

		$res = array();
		for ( $i=0; $i<$rows; $i++ )
			for ( $j=0; $j<$cols; $j++ )
		{
			list(,$len) = unpack ( "N*", substr ( $response, $p, 4 ) ); $p += 4;
			$res[$i][] = substr ( $response, $p, $len ); $p += $len;
		}

		$this->_MBPop ();
		return $res;
	}
}