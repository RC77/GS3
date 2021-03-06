<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301, USA.
\*******************************************************************/

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/pcre_check.php' );


$action = @$_REQUEST['action'];
if (! in_array($action, array( '', 'gedit', 'gsave', 'ggdel', 'gaddcid', 'gdelcid' ), true))
	$action = '';
$ggid = (int)@$_REQUEST['ggid'];



echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$gw_types = array(
	'sip'     => 'SIP',
	'iax'     => 'IAX',
	'misdn'   => 'ISDN (mISDN)',
	'woomera' => 'ISDN (Woomera)',
	//'zap'     => 'ISDN (Zaptel)',
	'dahdi'   => 'ISDN (Dahdi)',
	'capi'    => 'ISDN (CAPI)',
	'vpb'     => 'ISDN (VPB)',
);




#####################################################################
if ($action === 'gsave') {
	
	if ($ggid > 0) {
		$rs = $DB->execute(
'SELECT
	`name`,
	`title`,
	`type`,
	`allow_in`,
	`in_dest_search`,
	`in_dest_replace`,
	`in_cid_search`,
	`in_cid_replace`,
	`out_cid_search`,
	`out_cid_replace`
FROM
	`gate_grps`
WHERE `id`='.$ggid
		);
		$oldgg = $rs->fetchRow();
	}
	if ($ggid < 1 || ($ggid > 0 && ! $oldgg)) {
		$oldgg = array(
			'name'            => '',
			'title'           => '',
			'type'            => '',
			'allow_in'        => 0,
			'in_dest_search'  => '',
			'in_dest_replace' => '',
			'in_cid_search'   => '',
			'in_cid_replace'  => '',
			'out_cid_search'  => '',
			'out_cid_replace' => ''
		);
	}
	
	$title = trim(@$_REQUEST['gg-title']);
	//$name  = preg_replace('/[^a-z0-9\-_]/', '', @$_REQUEST['gg-name']);
	$name = str_replace(
		array('_', ' '),
		array('-', '-'),
		strToLower($title));
	$name = preg_replace('/[^a-z0-9\-]/', '', $name);
	$type = 'balance';
	$allow_in = (@$_REQUEST['gg-allow_in'] ? 1 : 0);
	
	$in_dest_search  = trim(@$_REQUEST['gg-in_dest_search' ]);
	$in_dest_replace = preg_replace('/[^0-9+a-zA-Z\-_.,$]/', '', trim(@$_REQUEST['gg-in_dest_replace']));
	if (! is_valid_pcre( '/'.$in_dest_search.'/', $in_dest_replace )) {
		echo '<div class="errorbox">',
			sPrintF(htmlEnt(__("Ung\xC3\xBCltiges Suchen/Ersetzen-Muster %s f\xC3\xBCr die eingehende Durchwahl wurde nicht gespeichert!")),
				'<tt>'.htmlEnt( 's/'.$in_dest_search.'/'.$in_dest_replace.'/' ).'</tt>'),
			'</div>',"\n";
		$in_dest_search  = $oldgg['in_dest_search' ];
		$in_dest_replace = $oldgg['in_dest_replace'];
	}
	
	$in_cid_search  = trim(@$_REQUEST['gg-in_cid_search' ]);
	$in_cid_replace = preg_replace('/[^0-9+a-zA-Z\-_.,$]/', '', trim(@$_REQUEST['gg-in_cid_replace']));
	if (! is_valid_pcre( '/'.$in_cid_search.'/', $in_cid_replace )) {
		echo '<div class="errorbox">',
			sPrintF(htmlEnt(__("Ung\xC3\xBCltiges Suchen/Ersetzen-Muster %s f\xC3\xBCr die eingehende Caller-ID wurde nicht gespeichert!")),
				'<tt>'.htmlEnt( 's/'.$in_cid_search.'/'.$in_cid_replace.'/' ).'</tt>'),
			'</div>',"\n";
		$in_cid_search  = $oldgg['in_cid_search' ];
		$in_cid_replace = $oldgg['in_cid_replace'];
	}
	
	$out_cid_search  = trim(@$_REQUEST['gg-out_cid_search' ]);
	$out_cid_replace = preg_replace('/[^0-9+a-zA-Z\-_.,$]/', '', trim(@$_REQUEST['gg-out_cid_replace']));
	if (! is_valid_pcre( '/'.$out_cid_search.'/', $out_cid_replace )) {
		echo '<div class="errorbox">',
			sPrintF(htmlEnt(__("Ung\xC3\xBCltiges Suchen/Ersetzen-Muster %s f\xC3\xBCr die ausgehende Caller-ID wurde nicht gespeichert!")),
				'<tt>'.htmlEnt( 's/'.$out_cid_search.'/'.$out_cid_replace.'/' ).'</tt>'),
			'</div>',"\n";
		$out_cid_search  = $oldgg['out_cid_search' ];
		$out_cid_replace = $oldgg['out_cid_replace'];
	}
	
	if ($ggid > 0) {
		$DB->execute(
'UPDATE `gate_grps` SET
	`title`=\''.           $DB->escape($title          ) .'\',
	`type`=\''.            $DB->escape($type           ) .'\',
	`allow_in`='.                      $allow_in         .',
	`in_dest_search`=\'' . $DB->escape($in_dest_search ) .'\',
	`in_dest_replace`=\''. $DB->escape($in_dest_replace) .'\',
	`in_cid_search`=\''  . $DB->escape($in_cid_search  ) .'\',
	`in_cid_replace`=\'' . $DB->escape($in_cid_replace ) .'\',
	`out_cid_search`=\'' . $DB->escape($out_cid_search ) .'\',
	`out_cid_replace`=\''. $DB->escape($out_cid_replace) .'\'
WHERE `id`='.$ggid
		);
		// separate query because there's a "unique" constraint on the name
		// column:
		$DB->execute(
'UPDATE `gate_grps` SET
	`name`=\''.            $DB->escape($name           ) .'\'
WHERE `id`='.$ggid
		);
	}
	else {
		$DB->execute(
'INSERT INTO `gate_grps` (
	`id`,
	`name`,
	`title`,
	`type`,
	`allow_in`,
	`in_dest_search`,
	`in_dest_replace`,
	`in_cid_search`,
	`in_cid_replace`,
	`out_cid_search`,
	`out_cid_replace`
) VALUES (
	NULL,
	\''. $DB->escape($name           ) .'\',
	\''. $DB->escape($title          ) .'\',
	\''. $DB->escape($type           ) .'\',
	'.               $allow_in         .',
	\''. $DB->escape($in_dest_search ) .'\',
	\''. $DB->escape($in_dest_replace) .'\',
	\''. $DB->escape($in_cid_search  ) .'\',
	\''. $DB->escape($in_cid_replace ) .'\',
	\''. $DB->escape($out_cid_search ) .'\',
	\''. $DB->escape($out_cid_replace) .'\'
)'
		);
		$ggid = (int)$DB->getLastInsertId();
		if ($ggid < 1) $ggid = 0;
		$_REQUEST['ggid'] = $ggid;
	}
	
	$cmd = '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null';
	@exec( 'sudo sh -c '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null &' );
	
	$action = 'gedit';
}
#####################################################################



#####################################################################
if ($action === 'ggdel') {
	
	$DB->execute( 'UPDATE `gates` SET `grp_id`=NULL WHERE `grp_id`='.$ggid );
	
	$DB->execute( 'DELETE FROM `gate_cids` WHERE `grp_id`='.$ggid );
	
	$DB->execute( 'DELETE FROM `gate_grps` WHERE `id`='.$ggid );
	
	$action = '';
}
#####################################################################



#####################################################################
if ($action === 'gaddcid') {
	
	$ggid = (int)@$_REQUEST['gg-id'];
	$cid_int = preg_replace( '/[^0-9]/', '', @$_REQUEST['cid-int']);
	$cid_ext = preg_replace( '/[^0-9]/', '', @$_REQUEST['cid-ext']);
	
	if (($ggid > 0) && ($cid_int != '')) {
		
		$DB->execute(
			'INSERT INTO `gate_cids` '.
				'(`grp_id`, `cid_int`, `cid_ext`) '.
			'VALUES '.
				'('.$ggid.', \''.$DB->escape($cid_int).'\', \''.$DB->escape($cid_ext).'\')'
			);
	}
	
	$action = 'gedit';
}
#####################################################################



#####################################################################
if ($action === 'gdelcid') {
	
	$ggid = (int)@$_REQUEST['gg-id'];
	$cid_int = preg_replace( '/[^0-9]/', '', @$_REQUEST['cid-int']);
	
	if (($ggid > 0) && ($cid_int != '')) {
		
		$DB->execute(
			'DELETE FROM `gate_cids` '.
			'WHERE '.
				'`grp_id` = '.$ggid.' AND '.
				'`cid_int` = \''.$DB->escape($cid_int).'\''
			);
	}
	
	$action = 'gedit';
}
#####################################################################



# get gateway groups from DB
#
$rs = $DB->execute( 'SELECT `id`, `name`, `title` FROM `gate_grps` ORDER BY `title`' );
$ggs = array();
while ($r = $rs->fetchRow())
	$ggs[] = $r;


?>
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="gedit" />
<?php
echo __('Gateway-Gruppe'),': ';
echo '<select name="ggid" onchange="this.form.submit();">',"\n";
foreach ($ggs as $gg) {
	echo '<option value="', $gg['id'] ,'"', ($gg['id']==$ggid ? ' selected="selected"' : '') ,'>', htmlEnt($gg['title']) ,' (', htmlEnt($gg['name']) ,')</option>',"\n";
}
echo '<option value="" disabled="disabled">-</option>',"\n";
echo '<option value="0"', (($ggid < 1 && $action != '') ? ' selected="selected"' : '') ,'>', __('Neue Gateway-Gruppe anlegen ...') ,'</option>',"\n";
echo '</select> ',"\n";
echo '<input type="submit" value="', __('anzeigen') ,'" />',"\n";
echo '</form>',"\n";
echo '<hr size="1" />',"\n";


if ($ggid > 0) {
	echo '<div class="fr">',"\n";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'ggid='.$ggid .'&amp;action=ggdel') ,'" title="', __('l&ouml;schen'), '"><button type="button"><img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /> ', __('l&ouml;schen') ,'</button></a>';
	echo '</div>',"\n";
}


#####################################################################
if ($action === 'gedit') {
	
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="ggid" value="<?php echo $ggid; ?>" />
<input type="hidden" name="action" value="gsave" />

<?php
	
	if ($ggid > 0) {
		# get gateway group from DB
		$rs = $DB->execute(
'SELECT
	`name`,
	`title`,
	`type`,
	`allow_in`,
	`in_dest_search`,
	`in_dest_replace`,
	`in_cid_search`,
	`in_cid_replace`,
	`out_cid_search`,
	`out_cid_replace`
FROM
	`gate_grps`
WHERE `id`='.$ggid
		);
		$gg = $rs->fetchRow();
		if (! $gg) return;
	}
	else {
		$gg = array(
			'name'           => '',
			'title'          => '',
			'type'           => 'balance',
			'allow_in'       => '',
			'in_dest_search' => '',
			'in_dest_replace'=> '',
			'in_cid_search'  => '',
			'in_cid_replace' => '',
			'out_cid_search' => '^(.*)',
			'out_cid_replace'=> '$1'
		);
	}
	
?>


<h3><?php echo __('Gateway-Gruppe'); ?></h3>
<table cellspacing="1">
<tbody>

<?php
	echo '<tr>',"\n";
	echo '<th style="width:90px;">', __('Titel') ,':</th>',"\n";
	echo '<th style="width:340px;"><input type="text" name="gg-title" value="', htmlEnt($gg['title']) ,'" size="30" maxlength="35" style="font-weight:bold; width:97%;" /></th>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Art') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<select name="gg-type" disabled="disabled">',"\n";
	echo '<option value="balance" selected="selected">Load Balance</option>',"\n";
	echo '</select>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>&nbsp;</th>',"\n";
	echo '<td>';
	echo '<input type="checkbox" name="gg-allow_in" id="ipt-gg-allow_in" value="1" ', ($gg['allow_in'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gg-allow_in">', __('eingehende Anrufe zulassen') ,'</label>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Ausgehende Caller-ID') ,':</th>',"\n";
	echo '<td>',"\n";
	echo __('Suchen/Ersetzen-Muster f&uuml;r ausgehende Caller-ID') ,' <sup><a href="#ftn-1" title="', htmlEnt(__("Fu\xC3\x9Fnote")) ,'">[1]</a></sup>:',"\n";
	echo '<div class="nobr" style="font-family:monospace;">',"\n";
	echo 's/<input type="text" name="gg-out_cid_search" value="', htmlEnt($gg['out_cid_search']) ,'" size="35" maxlength="255" style="font-family:monospace;" /><br />',"\n";
	echo '&nbsp;/<input type="text" name="gg-out_cid_replace" value="', htmlEnt($gg['out_cid_replace']) ,'" size="35" maxlength="255" style="font-family:monospace;" />/',"\n";
	echo '</div>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Eingehende Durchwahl') ,':</th>',"\n";
	echo '<td>',"\n";
	echo __('Suchen/Ersetzen-Muster (um Pr&auml;fix wegzuschneiden)') ,' <sup><a href="#ftn-2" title="', htmlEnt(__("Fu\xC3\x9Fnote")) ,'">[2]</a></sup>:',"\n";
	echo '<div class="nobr" style="font-family:monospace;">',"\n";
	echo 's/<input type="text" name="gg-in_dest_search" value="', htmlEnt($gg['in_dest_search']) ,'" size="35" maxlength="255" style="font-family:monospace;" /><br />',"\n";
	echo '&nbsp;/<input type="text" name="gg-in_dest_replace" value="', htmlEnt($gg['in_dest_replace']) ,'" size="35" maxlength="255" style="font-family:monospace;" />/',"\n";
	echo '</div>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Eingehende Caller-ID') ,':</th>',"\n";
	echo '<td>',"\n";
	echo __('Suchen/Ersetzen-Muster') ,' <sup><a href="#ftn-3" title="', htmlEnt(__("Fu\xC3\x9Fnote")) ,'">[3]</a></sup>:',"\n";
	echo '<div class="nobr" style="font-family:monospace;">',"\n";
	echo 's/<input type="text" name="gg-in_cid_search" value="', htmlEnt($gg['in_cid_search']) ,'" size="35" maxlength="255" style="font-family:monospace;" /><br />',"\n";
	echo '&nbsp;/<input type="text" name="gg-in_cid_replace" value="', htmlEnt($gg['in_cid_replace']) ,'" size="35" maxlength="255" style="font-family:monospace;" />/',"\n";
	echo '</div>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
?>

</tbody>
</table>

<h3><?php echo __('Gateways'); ?></h3>
<table cellspacing="1">
<thead>
<tr>
	<th style="width:100px;"><?php echo __('Typ'); ?></th>
	<th style="width:160px;"><?php echo __('Titel'); ?></th>
</tr>
</thead>
<tbody>
<?php
	# get gateways from DB
	#
	$rs = $DB->execute( 'SELECT `id`, `type`, `name`, `title`, `allow_out`, `dialstr` FROM `gates` WHERE `grp_id`='.$ggid.' ORDER BY `title`' );
	$i=0;
	while ($gw = $rs->fetchRow()) {
		echo '<tr class="', ($i%2?'even':'odd') ,'">',"\n";
		echo '<td>', (array_key_exists($gw['type'], $gw_types) ? @$gw_types[$gw['type']] : $gw['type']) ,'</td>',"\n";
		echo '<td>', htmlEnt($gw['title']) ,'</td>',"\n";
		echo '</tr>',"\n";
		++$i;
	}
?>
</tbody>
</table>

<br />
<button type="submit">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
	<?php echo __('Speichern'); ?>
</button>

</form>

<br />
<br />
<br />

<h3><?php echo __('Ausgehende Caller-IDs'); ?></h3>
<p><?php echo htmlEnt(__("Umsetzung interner Nebenstellen zu ausgehenden Caller-IDs f\xC3\xBCr diese Gateway-Gruppe")), ' <sup><a href="#ftn-2" title="', htmlEnt(__("Fu\xC3\x9Fnote")) ,'">[4]</a></sup>:'; ?></p>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="gaddcid" />
<input type="hidden" name="gg-id" value="<?php echo $ggid; ?>" />
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:200px;"><?php echo __('Nebenstelle'); ?></th>
	<th style="width:200px;"><?php echo __('Caller-ID (Durchwahl)'); ?></th>
	<th style="width:20px;"></th>
</tr>
</thead>
<tbody>

<?php
$rs = $DB->execute( 'SELECT `cid_int`, `cid_ext` FROM `gate_cids` WHERE `grp_id`='.$ggid );

if ((@$DB->numFoundRows()) < 1) {
	//echo '<tr><td><i>- ', __('keine'), ' -</i></td><td></td><td></td></tr>';
} else {
	while ($cid_map = $rs->fetchRow()) {
		echo '<tr>';
		echo '<td>', htmlEnt( $cid_map['cid_int'] ), '</td>';
		echo '<td>', htmlEnt( $cid_map['cid_ext'] ), '</td>';
		echo '<td>';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=gdelcid&amp;gg-id='.$ggid.'&amp;cid-int='.urlEncode($cid_map['cid_int'])), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>';
		echo '</tr>', "\n";
	}
}

echo '<tr>';
echo '<td>';
echo '<input type="text" name="cid-int" value="" size="20" maxlength="30" />';
echo '</td>';
echo '<td>';
echo '<input type="text" name="cid-ext" value="" size="20" maxlength="30" />';
echo '</td>';
echo '<td>';
echo '<button type="submit" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
echo '</td>';
echo '</tr>';
?>

</tbody>
</table>
</form>


<br />
<br />
<br />

<?php
	echo '<a name="ftn-1"></a>',"\n";
	echo '<p id="ftn-1" class="text"><sup>[1]</sup> ', sPrintF(
		__('Suchen/Ersetzen-Muster (<a href="%s" target="_blank">PCRE</a>) f&uuml;r die Rufnummern&uuml;bermittlung bei abgehenden Anrufen. Beispiele:<br /> Nur die Durchwahl &uuml;bermitteln: <tt>s/^(.*)/$1/</tt><br /> Nationales Format: <tt>s/^(.*)/030123456$1/</tt><br /> Internationales Format: <tt>s/^(.*)/004930123456$1/</tt> oder <tt>s/^(.*)/+4930123456$1/</tt><br /> F&uuml;r alle Benutzer die gleiche Nummer &uuml;bertragen: <tt>s/^(.*)/00493012345612/</tt><br /> Normalerweise sollten Sie das nationale oder internationale Format verwenden.'),
		__('http://de.wikipedia.org/wiki/Regul%C3%A4rer_Ausdruck')
	) ,'</p>',"\n";
	
	echo '<a name="ftn-2"></a>',"\n";
	echo '<p id="ftn-2" class="text"><sup>[2]</sup> ', sPrintF(
		__('Geben Sie hier falls erforderlich ein <a href="%s" target="_blank">PCRE</a>-Muster an, das eventuelle Pr&auml;fixe von eingehend gew&auml;hlten Nummern wegschneidet, soda&szlig; nur noch die interne Durchwahl &uuml;brig bleibt! Beispiele:<br /> &nbsp;&nbsp;&nbsp; <tt>s/^026313370//</tt><br /> &nbsp;&nbsp;&nbsp; <tt>s/^(((0049|0)2631))3370//</tt><br /> &nbsp;&nbsp;&nbsp; <tt>s/^(?:(?:0049|0)2631)?3370(.*)/$1/</tt>'),
		__('http://de.wikipedia.org/wiki/Regul%C3%A4rer_Ausdruck')
	) ,'</p>',"\n";
	
	echo '<a name="ftn-3"></a>',"\n";
	echo '<p id="ftn-3" class="text"><sup>[3]</sup> ', sPrintF(
		__('Suchen/Ersetzen-Muster (<a href="%s" target="_blank">PCRE</a>) f&uuml;r die Rufnummern&uuml;bermittlung bei eingehenden Anrufen. Hiermit lassen sich Anrufernummern die vom Provider falsch &uuml;bermittelt werden korrigieren. Beispiel: <tt>s/^/0/</tt> oder <tt>s/^(.*)/0$1/</tt> um eine 0 an den Anfang zu setzen. Leer f&uuml;r keine Ersetzung.'),
		__('http://de.wikipedia.org/wiki/Regul%C3%A4rer_Ausdruck')
	),
	'<br />', __('Experimentell'), ': ',
	__('Sie k&ouml;nnen diese Feld auch verwenden um f&uuml;r die Anruflisten mit <tt>s/^/0/</tt> oder <tt>s/^(.*)/0$1/</tt> eine 0 am Anfang hinzuzuf&uuml;gen oder mit <tt>s/^0//</tt> oder <tt>s/^0(.*)/$1/</tt> eine 0 am Anfang wegzuschneiden.')
	,'</p>',"\n";
	
	echo '<a name="ftn-4"></a>',"\n";
	echo '<p id="ftn-4" class="text"><sup>[4]</sup> ', sPrintF(
		__('Tragen Sie pro Zeile eine interne Caller-ID (Nebenstellennummer) ein, die Sie beim W&auml;hlen &uuml;ber diese Gateway-Gruppe zu einer externen Caller-ID (Durchwahlnummer) umsetzen m&ouml;chten, z.B. um pro Benutzer eine abgehende <a href="%s" target="_blank">MSN</a> festzulegen.'),
		__('http://de.wikipedia.org/wiki/Vermittlungstechnische_Leistungsmerkmale_%28%C3%B6ffentliche_Netze%29#Mehrfachrufnummern_.28MSN.29_bei_Mehrger.C3.A4teanschluss')
	) ,'</p>',"\n";
	
}

#####################################################################

?>
