<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

// Stringhe in italiano per local_timemachine.

$string['pluginname'] = 'Moodle Time Machine';
$string['timemachine:manage'] = 'Gestisci Moodle Time Machine';
$string['managebackups'] = 'Gestione backup dei corsi';
$string['task_backup_courses'] = 'Esegui backup Moodle Time Machine';
$string['task_send_summary'] = 'Invia email di riepilogo Moodle Time Machine';
$string['setting_categoryids'] = 'Categorie da includere nel backup';
$string['setting_categoryids_desc'] = 'Se impostate, tutti i corsi nelle categorie selezionate verranno inclusi nei backup automatici.';
$string['setting_ftpenabled'] = 'Abilita upload FTP';
$string['setting_ftpenabled_desc'] = 'Se abilitato, ogni backup viene caricato anche sul server FTP configurato.';
$string['setting_ftphost'] = 'Host FTP';
$string['setting_ftpport'] = 'Porta FTP';
$string['setting_ftpuser'] = 'Utente FTP';
$string['setting_ftppass'] = 'Password FTP';
$string['setting_ftppath'] = 'Percorso FTP';
$string['setting_ftppassive'] = 'Usa modalita\' passiva';
$string['setting_verbose'] = 'Log dettagliati';
$string['setting_verbose_desc'] = 'Includi messaggi di avanzamento dettagliati e stack trace nei log del task pianificato.';
$string['setting_retentionversions'] = 'Retention (versioni per corso)';
$string['setting_retentionversions_desc'] = 'Numero massimo di versioni di backup da mantenere per ciascun corso. Le versioni piu\' vecchie vengono eliminate automaticamente. Default: 7.';
$string['setting_backup_users'] = 'Includi utenti';
$string['setting_backup_role_assignments'] = 'Includi assegnazioni di ruolo';
$string['setting_backup_activities'] = 'Includi attivita\'';
$string['setting_backup_blocks'] = 'Includi blocchi';
$string['setting_backup_filters'] = 'Includi filtri';
$string['setting_backup_comments'] = 'Includi commenti';
$string['setting_backup_badges'] = 'Includi badge';
$string['setting_backup_calendarevents'] = 'Includi eventi calendario';
$string['setting_backup_userscompletion'] = 'Includi completamento utenti';
$string['setting_notifyonfail'] = 'Notifica in caso di fallimenti ripetuti';
$string['setting_notifyfailthreshold'] = 'Soglia per notifica fallimenti';
$string['setting_notifyfailthreshold_desc'] = 'Dopo questo numero di fallimenti consecutivi del backup per un corso, invia una email all\'amministratore con i dettagli dell\'errore.';
$string['searchcourses'] = 'Cerca corsi';
$string['backupdate'] = 'Data backup';
$string['size'] = 'Dimensione';
$string['deletecourseall'] = 'Elimina tutto per il corso';
$string['privacy:metadata'] = 'Moodle Time Machine non memorizza dati personali.';
$string['backupfailed'] = 'Backup non riuscito.';
$string['expandversions'] = 'Mostra tutte le versioni';
$string['collapseversions'] = 'Nascondi versioni';

// Interfaccia log.
$string['log_title'] = 'Log dei fallimenti di backup';
$string['log_since'] = 'Da (timestamp UNIX)';
$string['log_courseid'] = 'ID corso';
$string['log_empty'] = 'Nessuna voce di log per il filtro selezionato.';
$string['log_time'] = 'Orario';
$string['log_course'] = 'Corso';

// Email di riepilogo.
$string['summary_email_subject'] = 'Moodle Time Machine: riepilogo giornaliero backup';
$string['summary_email_since'] = 'Inizio periodo: {$a}';
$string['summary_email_successes'] = 'Backup riusciti: {$a}';
$string['summary_email_totalmb'] = 'Dimensione totale (MB): {$a}';
$string['summary_email_fail_header'] = 'Corsi con backup non riuscito:';
$string['summary_email_no_fail'] = 'Nessun backup fallito in questo periodo.';
$string['summary_email_loglink'] = 'Vedi log dettagliati: {$a}';

// Statistiche e conferme UI.
$string['stat_courses'] = 'Corsi sottoposti a backup';
$string['stat_totalsize'] = 'Spazio totale su disco occupato dai backup';
$string['stat_since_last'] = 'Backup dall\'ultimo riepilogo';
$string['stat_never'] = 'mai inviato';
$string['stat_versions'] = 'Versioni di backup: {$a}';
$string['stat_courses_detail'] = 'Corsi con storico: {$a}';
$string['confirm_delete_backup'] = 'Eliminare questo backup? L\'azione non e\' reversibile.';
$string['confirm_delete_course'] = 'Eliminare TUTTI i backup di questo corso? L\'azione non e\' reversibile.';
$string['clearsearch'] = 'Pulisci';
