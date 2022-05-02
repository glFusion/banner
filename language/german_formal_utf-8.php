<?php
/**
*   Default German Language file for the Banner plugin, addressing the user as "Sie"
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
*   @translated by Siegfried Gutschi (November 2017) <sigi AT modellbaukalender DOT info>
*   @package    banner
*   @version    0.2.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

global $LANG32;

/**
* The plugin's lang array
* @global array $LANG_BANNER
*/
$LANG_BANNER = array(
    'pi_name' => 'Banner',
'bid'           => 'ID',
'cid'           => 'ID',
'camp_id'       => 'Kampagne',
'target_url'    => 'Ziel-URL',
'target'        => 'Ziel',
'new_window'    => 'Neues Fenster',
'same_window'   => 'Gleiches Fenster',
'banner_url'    => 'Banner Bild-URL',
'upload_img'    => 'Banner hochladen',
'remote_img'    => 'Externe Bild-Url',
'includehttp'   => 'Include http://',
'alt'           => 'Alt-Tag',
'wide'          => 'Breite',
'high'          => 'Höhe',
'max'           => 'Maximal',
'dimensions'    => 'Abmessungen',
'upload_vs_url' => 'Es können ein Hochgeladenes-Banner als auch ein Externes-Banner angegeben werden. Wenn beide angegeben sind, wird das Hochgeladene-Banner bevorzugt.',
'current_image' => 'Aktuelles Bild',
'ad_campaigns'  => 'Werbekampagnen',
'campaigns'     => 'Kampagnen',
'campaign'      => 'Kampagne',
'ad_code'       => 'Banner-Code',
'ad_is_script'  => 'Skriptanzeigen werden nicht angezeigt',
'ad_type'       => 'Art der Anzeige',
'ad_types'      => array(
    'Hochgeladenes-Banner',
    'Externes-Banner',
    'HTML oder Javascript',
    'Autotag',
),
'ok_to_delete'  => 'Möchten Sie diesen Eintrag wirklich löschen?',
'banner_content' => 'Banner-Inhalt',
'hits'          => 'Klicks',
'ads_in_campaign' => 'Banner dieser Kapagne',
'action'        => 'Aktion',
'edit'          => 'Bearbeiten',
'banners'       => 'Banner',
'visible_to'    => 'Sichtbar für',
'visible_members' => 'Sichtbar für Mitglieder?',
'visible_anon' => 'Sichtbar für Gäste?',
'access_denied' => 'Zugriff verweigert',
'access_denied_msg' => 'Leider haben Sie keinen Zugriff auf dies Seite. Bitte beachten Sie, dass alle nicht autorisierten Zugriffe protokolliert werden.',
'banner_editor' => 'Banner-Editor',
'banner_id'     => 'ID',
'banner_title'  => 'Titel',
'banner_cat'    => 'Kategorie',
'pubstart'      => 'Start',
'pubend'        => 'Ende',
'banner_hits'   => 'Banner-Klicks',
'new_banner'    => 'Neues Banner',
'validate_banner' => 'Banner überprüfen',
'categories'    => 'Kategorien',
'validate_now'  => 'Jetzt überprüfen',
'html_status'   => 'HTML-Status',
'html_status_na' => 'Die HTTP-Antwort wird nicht überprüft für HTML- oder Script-Banner bzw. Banner ohne konfigurierte URL.',
'validate_instr' => '<p> Um alle Banner zu überprüfen, klicken Sie bitte auf &quot;Jetzt überprüfen&quot;. Dies kann, je nach Anzahl der Banner, einige Zeit in Anspruch nehmen.</p>',
'banner_mgr'    => 'Banner-Verwaltung',
'banner_instr_list' => 'Um ein Banner zu bearbeiten oder zu löschen, klicken Sie unten auf das Bearbeitungssymbol. Um ein neues Banner zu erstellen, klicken Sie oben auf &quot;Neues Banner&quot;.',
'banner_instr_validate' => 'Klicken Sie auf die &quot;Jetzt überprüfen&quot; Schaltfläche, um die HTTP-Antwort der Banner-Links zu überprüfen.',
'enabled'       => 'Aktiviert',
'centerblock'   => 'Zentrumsblock',
'click_disable' => 'Anklicken zum deaktivieren',
'click_enable'  => 'Anklicken zum aktivieren',
'before_validate' => 'Noch nicht überprüft',
'camp_id'       => 'ID',
'user_id'       => 'Benutzer',
'banners'       => 'Banner',
'new_camp'      => 'Neue Kampagne',
'camp_mgr'      => 'Kampagnen-Verwaltung',
'camp_mgr_instr' => 'Kampagnen hinzufügen, löschen oder bearbeiten.',
'cat_mgr_instr' => 'Kategorien hinzufügen, löschen oder bearbeiten.',
'all'           => 'Alle',
'new_cat'       => 'Neue Kategorie',
'type'          => 'Typ',
'cat_name'      => 'Kategorie',
'topic'         => 'Thema',
'cat_mgmt'      => 'Kategorie-Verwaltung',
'banner_mgmt'   => 'Banner-Verwaltung',
'category'      => 'Kategorie',
'description'   => 'Beschreibung',
'edit_details'  => 'Daten eingeben bzw. bearbeiten.',
'title'         => 'Titel',
'banner_info'   => 'Banner-Informationen',
'max_hits'      => 'Max. Klicks',
'impressions'   => 'Aufrufe',
'max_impressions' => 'Max. Aufrufe',
'access_control' => 'Zugriffskontrolle',
'submit_banner' => 'Banner einsenden',
'banner_submissions' => 'Banner-Einsendungen',
'stats_headline'    => 'Top 10 Banner',
'stats_page_title'  => 'Banner',
'stats_no_hits'     => 'Es wurden noch keine aktiven Banner angeklickt.',
'weight'        => 'Gewichtung',
'max_img_height'    => 'Max. Bild-Höhe (px)',
'max_img_width'    => 'Max. Bild-Breite (px)',
'duplicate_bid'     => 'Die Banner-ID ist bereits vergeben.',
'duplicate_cid'     => 'Die Kategorie-ID ist bereits vergeben.',
'duplicate_camp_id' => 'Die Kampagne-ID ist bereits vergeben.',
'no_dt_limit'       => 'Keine Datums-Beschränkung',
'chk_rem_dt_limit' => 'Check to remove date limitation',
'reset'             => 'Reset',
'confirm_delitem'   => 'Möchten Sie diesen Eintrag wirklich löschen?',
'req_item_msg'  => ' = Pflichtfelder',
'user_can_add'  => 'Mitglider können Banner einsenden',
'version'       => 'Version',
'err_invalid_url' => 'Ungültige Ziel-URL angegeben.',
'err_invalid_image_url' => 'Ungültige Bild-URL für Externes-Banner angegeben.',
'err_missing_upload' => 'Es wurde kein Banner hochgeladen',
'err_missing_adcode' => 'Banner Code darf nicht leer sein bei wenn &quot;HTML oder Javascript&quot; bzw. &quot;Autotag&quot; ausgewählt wurde.',
'err_missing_title' => 'Es wurde kein Titel angegeben.',
'err_empty_id'  => 'Es wurde keine ID angegeben.',
'err_saving_item'   => 'Es gab einen Fehler beim speichern dieses Eintrages.',
'err_dup_id' => 'Diese ID ist bereits vergeben.',
'unknown' => 'Unbekannt',
'msg_item_enabled' => 'Eintrag wurde aktiviert',
'msg_item_disabled' => 'Eintrag wurde deaktiviert',
'msg_item_nochange' => 'Eintrag wurde nicht verändert',
'select_date' => 'Datum wählen',
'required' => 'Erforderlich',
'template' => 'Plugin',
'show_once' => 'Einmalig',
'position' => 'Position',
'show_in_content' => 'Im Inhalt',
'hlp_map_enabled' => 'Markieren um Banner dieser Kategorie in diesem Plugin anzuzeigen.',
'hlp_map_pos' => 'Position der Banner in Plugin-Seiten. 0 = Keine Banner, 1 = nach dem ersten Artikel, 2 = nach dem zweiten Artikel, usw.',
'hlp_map_once' => 'Wenn &quot;Einmalig&quot; aktiviert ist, wird nur ein Banner pro Seite angezeigt. Andernfalls werden die Banner -wie unter &quot;Position&quot; ausgewählt- im Intervall angezeigt. 2 = nach jedem zweiten Artikel, 3 = nach jedem dritten Artikel, usw.',
'hlp_map_content' => 'Wenn Banner im Hauptinhalt angezeigt werden sollen muss &quot;Im Inhalt&quot; aktiviert sein.',
'homeonly' => 'Homepage',
'broken_txt1' => 'Folgende Banner wurden als fehlerhaft gemeldet: ',
'broken_report_by' => 'Fehlerhaftes Banner wurde gemeldet von: ',
'click_to_edit' => 'Klicke hier um das Banner zu bearbeiten: ',
'thanks_for_report' => 'Danke, dass Du dieses fehlerhafte Banner gemeldet hast. Der Administrator wird das Problem so schnell wie möglich beheben.',
'thanks' => 'Danke',
'broken_report' => 'Fehlerhaftes Banner melden',
'root' => 'Wurzel',   // title used for top level category
'warn_update_hits' => 'Aktualisieren kann die Kampagnenberichterstattung beschädigen',
'zero_eq_unlimited' => '0 = Unlimitiert',
'cannot_delete' => 'Cannot delete required or used categories or campaigns',
'alert' => 'Alert',
'warning' => 'Warning',
'admin_hdr' => 'Banner Administration',
'at_dscp_banner' => 'Include a specific banner in the content',
'at_dscp_randombanner' => 'Include a random banner in the content',
'at_dscp_bannercategory' => 'Include a random banner from a specific category in the content',
'validate' => 'Validate',
'show_owner' => 'Show ads to the owner?',
'show_admins' => 'Show ads to administrators?',
'show_adm_pages' => 'Include in admin pages?',
'render_size_dscp' => 'Displayed size %d pixels wide by %d pixels high,<br />based on category and image size limits.',
'confirm_delitems' => 'Are you sure you want to delete these items?',
'reset_hits' => 'Reset hits and impressions',
'bulk_delete' => 'Delete selected items',
'q_reset_hits' => 'Are you sure you want to reset the hits and impressions?',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_banner_MESSAGE1 = "Vielen dank für Ihre Einsendung. Die Mitarbeiter von {$_CONF['site_name']} werden Ihre <a href={$_CONF['site_url']}/banner/index.php>Banner</a>-Einsendung so schnell wie möglich überprüfen.";
$PLG_banner_MESSAGE2 = 'Ihr Banner wurde erfolgreich gespeichert.';
$PLG_banner_MESSAGE3 = 'Das Banner wurde erfolgreich gelöscht.';
$PLG_banner_MESSAGE4 = "{$_CONF['site_name']} bedankt sich für Ihre Banner-Einsendung.  Sie finden Ihre Banner in der <a href={$_CONF['site_url']}/banner/index.php>Banner</a>-Verwaltung.";
$PLG_banner_MESSAGE5 = 'Sie haben nicht genügend Rechte um diese Kategorie anzuzeigen.';
$PLG_banner_MESSAGE6 = 'Sie haben nicht genügend Rechte um diese Kategorie zu bearbeiten.';
$PLG_banner_MESSAGE7 = 'Bitte geben Sie einen Kategorie-Namen und eine Beschreibung an.';

$PLG_banner_MESSAGE10 = 'Ihre Kategorie wurde erfolgreich gespeichert.';
$PLG_banner_MESSAGE11 = 'Die ID einer Kategorie darf weder &quot;site&quot; noch &quot;user&quot; heißen - Diese Namen sind für den internen Gebrauch reserviert.';
$PLG_banner_MESSAGE12 = 'Sie versuchen aus einer übergeordnete Kategorie einen Eintrag seiner eigenen Unterkategorie zu machen. Dies würde eine verwaiste Kategorie erzeugen. Bitte verschieben Sie  zuerst die untergeordneten Kategorien auf eine höhere Ebene.';
$PLG_banner_MESSAGE13 = 'Die Kategorie wurde erfolgreich gelöscht.';
$PLG_banner_MESSAGE14 = 'Die Kategorie enthält Banner oder Unterkategorien. Bitte entfernen Sie diese zuerst.';
$PLG_banner_MESSAGE15 = 'Sie haben nicht genügend Rechte um diese Kategorie zu löschen.';
$PLG_banner_MESSAGE16 = 'Diese Kategorie existiert nicht.';
$PLG_banner_MESSAGE17 = 'Diese Kategorie-ID existiert bereits.';

// Messages for the plugin upgrade
$PLG_banner_MESSAGE3001 = 'Plugin-Aktualisierung wird nicht unterstützt.';
$PLG_banner_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/banner.php
/**
* the banner plugin's lang admin array
*
* @global array $LANG_BANNER_ADMIN
*/
$LANG_BANNER_ADMIN = array(
    4 => 'Banner URL',
    5 => 'Kategorie',
    6 => '(inklusive http://)',
    7 => 'Sonstiges',
    9 => 'Banner-Inhalt',
    10 => 'Banner-Titel, Banner-URL und Beschreibung sind erforderlich.',
    20 => 'Wenn Sonstiges, bitte angeben',
    21 => 'Speichern',
    22 => 'Abbrechen',
    23 => 'Löschen',
    24 => 'Banner nicht gefunden',
    25 => 'Das für die Bearbeitung ausgewählte Banner wurde nicht gefunden.',
    28 => 'Kategorie bearbeiten',
    30 => 'Kategorie',
    32 => 'Kategorie-ID',
    33 => 'Kategorie löschen',
    34 => 'Hauptkategorie',
    35 => 'Kategorie speichern',
    36 => 'Kampagne speichern',
    37 => 'Kampagne löschen',
    40 => 'Diese Kategorie bearbeiten',
    41 => 'Unterkategorie erstellen',
    42 => 'Diese Kategorie löschen',
    43 => 'Website-Kategorien',
    44 => 'Eintrag hinzufügen',
    46 => 'Benutzer %s versuchte unerlaubt eine Kategorie zu löschen.',
    53 => 'Banner Banner',
    55 => 'Achtung: Sie können keine Kategorie löschen, die andere Kategorien oder Banner enthalten. Um Kategorien löschen zu können müssen Sie deren Inhalt in eine andere Kategorie verschieben.',
    56 => 'Kategorie-Editor',
    60 => 'Benutzer %s versuchte unerlaubt die Kategorie %s zu berabeiten.',
    66 => 'Kampagnen',
    72 => 'Aktion',
);

$LANG_BANNER_STATUS = array(
    0   => 'Suchfehler',
    100 => 'Weiter',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Erstellt',
    202 => 'Akzeptiert',
    203 => 'Non-Authoritative Information',
    204 => 'Kein Inhalt',
    205 => 'Inhalt zurücksetzen',
    206 => 'Teil-Inhalt',
    300 => 'Mehrfach-Auswahl',
    301 => 'Permanent verschoben',
    302 => 'Gefunden',
    303 => 'Siehe Sonstiges',
    304 => 'Nicht geändert',
    305 => 'Proxy benutzen',
    307 => 'Temporäre Weiterleitung',
    400 => 'Ungültige Anfrage',
    401 => 'Nicht autorisiert',
    402 => 'Zahlung erforderlich',
    403 => 'Verboten',
    404 => 'Nicht gefunden',
    405 => 'Nicht erlaubt',
    406 => 'Inakzeptabel',
    407 => 'Proxyauthentifizierung erforderlich',
    408 => 'Zeitüberschreitung der Anforderung',
    409 => 'Konflikt',
    410 => 'Weg',
    411 => 'Länge erforderlich',
    412 => 'Voraussetzung nicht erfüllt',
    413 => 'Angeforderter Wert zu groß',
    414 => 'Angeforderter-URI zu lang',
    415 => 'Nicht untestützter Medien Typ',
    416 => 'Angeforderter Bereich ungültig',
    417 => 'Erwartung fehlgeschlagen',
    500 => 'Interner Server Error',
    501 => 'Nicht implementiert',
    502 => 'Bad Gateway',
    503 => 'Service nicht verfügbar',
    504 => 'Gateway Zeitüberschreitung',
    505 => 'HTTP Version wird nicht unterstützt',
    999 => 'Verbindung Zeitüberschreitung',
);


// Localization of the Admin Configuration UI
$LANG_configsections['banner'] = array(
    'label' => 'Banner',
    'title' => 'Banner-Einstellungen',
);

$LANG_confignames['banner'] = array(
    'templatevars' => 'Banner ins Design integrieren',
    'usersubmit' => 'Mitglieder Einsendungen',
    'notification' => 'E-Mail Benachrichtigung',
    'delete_banner' => 'Banner mit Benutzer löschen',
    'default_permissions' => 'Banner Standart-Rechte',
    'show_in_admin' => 'Banner auf Admin-Seiten',
    'target_blank' => 'Links in neuen Fenster öffnen',
    'img_max_width' => 'Max. Banner-Breite (px)',
    'img_max_height' => 'Max. Banner-Höhe (px)',
    'users_dontshow' => 'Keine Banner für Benutzer',
    'ipaddr_dontshow' => 'Keine Banner für IP-Adresse',
    'uagent_dontshow' => 'Keine Banner für User-Agent',
    'def_weight'    => 'Standart-Gewichtung',
    'adshow_owner'  => 'Banner anzeigen für Besitzer',
    'adshow_admins' => 'Banner anzeigen für Admin',
    'cntclicks_owner' => 'Klicks des Besitzer zählen',
    'cntclicks_admins' => 'Klicks des Admin zählen',
    'cntimpr_owner' => 'Aufrufe des Besitzer zählen',
    'cntimpr_admins' => 'Aufrufe des Admin zählen',
    'cb_enable'     => 'Zentrumsblock aktivieren',
    'cb_home'       => 'Zentrumsblock nur auf Startseite',
    'cb_pos'        => 'Zentrumsblock Position',
    'cb_replhome'   => 'Zentrumsblock ersetzt Startseite',
    'block_limit'   => 'Max. Banner in Blöcken',
    'defgrpsubmit'  => 'Standart-Gruppe Kategorie/Kampagne',
);

$LANG_configsubgroups['banner'] = array(
    'sg_main' => 'Haupteinstellungen',
);

$LANG_fs['banner'] = array(
    'fs_main' => 'Allgemeine-Einstellungen',
    'fs_adcontrol' => 'Anzeige-Einstellungen',
    'fs_permissions' => 'Standard-Berechtigungen',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['banner'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => TRUE, 'Nein' => FALSE),
    3 => array('Ja' => 1, 'Nein' => 0),
    4 => array('10' => 10, '09' => 9, '08' => 8, '07' => 7, '06' => 6,
            '05' => 5, '04' => 4, '03' => 3, '02' => 2, '01' => 1),
    5 => array('Auf Seite oben' => 1, 'Nach Hauptartikel' => 2, 'Auf Seite unten' => 3),
    9 => array('Zur Banner-Seite' => 'item', 'Banner-Verwaltung' => 'list', 'Banner anzeigen' => 'plugin', 'Startseite' => 'home', 'Admin-Bereich' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur Lesen' => 2, 'Lesen-Schreiben' => 3),
);

?>
