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

/**
 * Stringhe in italiano per local_timemachine.
 *
 * @package   local_timemachine
 * @copyright 2025 GiDA
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backupdate'] = 'Data backup';
$string['backupfailed'] = 'Backup non riuscito.';
$string['clearsearch'] = 'Pulisci';
$string['collapseversions'] = 'Nascondi versioni';
$string['confirm_delete_backup'] = 'Eliminare questo backup? L\'azione non e\' reversibile.';
$string['confirm_delete_course'] = 'Eliminare TUTTI i backup di questo corso? L\'azione non e\' reversibile.';
$string['deletecourseall'] = 'Elimina tutto per il corso';
$string['email_fail_course'] = 'Corso: {$a}';
$string['email_fail_failures'] = 'Fallimenti: {$a}';
$string['email_fail_subject'] = 'Moodle Time Machine: backup fallito per il corso ID {$a}';
$string['email_fail_trace'] = 'Trace:';
$string['expandversions'] = 'Mostra tutte le versioni';
$string['log_course'] = 'Corso';
$string['log_courseid'] = 'ID corso';
$string['log_course_with_id'] = '{$a->name} (ID {$a->id})';
$string['log_courseid_only'] = 'ID {$a}';
$string['log_empty'] = 'Nessuna voce di log per il filtro selezionato.';
$string['log_error_prefix'] = 'Errore: {$a}';
$string['log_exception'] = 'Eccezione: {$a}';
$string['log_insert_failed'] = 'Inserimento log non riuscito ({$a})';
$string['log_since'] = 'Da (timestamp UNIX)';
$string['log_backup_failed_course'] = 'Backup non riuscito per il corso {$a}';
$string['log_backup_not_writable'] = 'Il file di backup non e\' scrivibile e non puo\' essere eliminato';
$string['log_current_signature'] = 'Firma attuale: {$a}';
$string['log_time'] = 'Orario';
$string['log_title'] = 'Log dei fallimenti di backup';
$string['log_previous_signature'] = 'Firma precedente: {$a}';
$string['log_signature_none'] = '(nessuna)';
$string['log_signature_query_failed'] = 'Query per la firma ({$a}) non riuscita';
$string['log_storage_unavailable'] = 'Directory di archiviazione non disponibile';
$string['log_unable_resolve_storage_delete'] = 'Impossibile risolvere la directory di archiviazione per l\'eliminazione';
$string['log_skip_delete_outside'] = 'Eliminazione ignorata: file fuori dalla directory di archiviazione';
$string['log_delete_failed'] = 'Impossibile eliminare il file di backup dal disco';
$string['log_no_category_selected'] = 'Nessuna categoria selezionata; salto';
$string['log_selected_categories'] = 'ID categoria selezionati: {$a}';
$string['log_found_courses'] = 'Corsi trovati: {$a}';
$string['log_queue_check_course'] = 'Verifica di coda per il corso id={$a->id} shortname={$a->shortname}';
$string['log_queue_error_course'] = 'Errore di coda per il corso {$a}';
$string['log_no_changes_skip'] = 'Nessuna modifica dall\'ultimo backup; coda ignorata';
$string['log_pending_adhoc'] = 'Gia\' presente nella coda adhoc; ignorato';
$string['log_recently_queued'] = 'Gia\' messo in coda di recente; ignorato';
$string['log_queued_task'] = 'Task adhoc di backup accodato';
$string['log_executing_backup'] = 'Esecuzione del piano di backup...';
$string['log_backup_controller_destroy_failed'] = 'Chiusura del controller di backup non riuscita ({$a})';
$string['log_saved_file'] = 'File salvato in {$a->path} dimensione={$a->size}';
$string['log_recorded_backup_entry'] = 'Voce di backup registrata nel DB';
$string['log_requeued_with_delay'] = 'Task adhoc rimesso in coda con ritardo {$a->delay}s (failcount={$a->failcount})';
$string['log_retention_deleting'] = 'Retention: eliminazione di {$a} backup vecchi';
$string['log_ftp_skip_missing'] = 'Upload FTP ignorato: file di backup mancante o fuori dalla directory di archiviazione';
$string['log_ftp_missing_functions'] = 'Funzioni FTP non disponibili in PHP';
$string['log_ftp_connect_failed'] = 'Connessione FTP non riuscita';
$string['log_ftp_login_failed'] = 'Login FTP non riuscito';
$string['log_ftp_change_dir_failed'] = 'Cambio directory FTP non riuscito';
$string['log_ftp_open_failed'] = 'Impossibile aprire il file di backup per l\'upload FTP';
$string['log_ftp_upload_ok'] = 'Upload FTP ok: {$a}';
$string['log_ftp_upload_failed'] = 'Upload FTP non riuscito';
$string['managebackups'] = 'Gestione backup dei corsi';
$string['pluginname'] = 'Moodle Time Machine';
$string['privacy:metadata'] = 'Moodle Time Machine non memorizza dati personali.';
$string['searchcourses'] = 'Cerca corsi';
$string['stat_backups_generated'] = 'Backup generati';
$string['setting_backup_activities'] = 'Includi attivita\'';
$string['setting_backup_badges'] = 'Includi badge';
$string['setting_backup_blocks'] = 'Includi blocchi';
$string['setting_backup_calendarevents'] = 'Includi eventi calendario';
$string['setting_backup_comments'] = 'Includi commenti';
$string['setting_backup_filters'] = 'Includi filtri';
$string['setting_backup_role_assignments'] = 'Includi assegnazioni di ruolo';
$string['setting_backup_users'] = 'Includi utenti';
$string['setting_backup_userscompletion'] = 'Includi completamento utenti';
$string['setting_categoryids'] = 'Categorie da includere nel backup';
$string['setting_categoryids_desc'] = 'Se impostate, tutti i corsi nelle categorie selezionate verranno inclusi nei backup automatici.';
$string['setting_ftpenabled'] = 'Abilita upload FTP';
$string['setting_ftpenabled_desc'] = 'Se abilitato, ogni backup viene caricato anche sul server FTP configurato.';
$string['setting_ftphost'] = 'Host FTP';
$string['setting_ftppass'] = 'Password FTP';
$string['setting_ftppassive'] = 'Usa modalita\' passiva';
$string['setting_ftppath'] = 'Percorso FTP';
$string['setting_ftpport'] = 'Porta FTP';
$string['setting_ftpuser'] = 'Utente FTP';
$string['setting_notifyfailthreshold'] = 'Soglia per notifica fallimenti';
$string['setting_notifyfailthreshold_desc'] = 'Dopo questo numero di fallimenti consecutivi del backup per un corso, invia una email all\'amministratore con i dettagli dell\'errore.';
$string['setting_notifyonfail'] = 'Notifica in caso di fallimenti ripetuti';
$string['setting_retentionversions'] = 'Retention (versioni per corso)';
$string['setting_retentionversions_desc'] = 'Numero massimo di versioni di backup da mantenere per ciascun corso. Le versioni piu\' vecchie vengono eliminate automaticamente. Default: 7.';
$string['setting_verbose'] = 'Log dettagliati';
$string['setting_verbose_desc'] = 'Includi messaggi di avanzamento dettagliati e stack trace nei log del task pianificato.';
$string['size'] = 'Dimensione';
$string['stat_courses'] = 'Corsi sottoposti a backup';
$string['stat_courses_detail'] = 'Corsi con backup: {$a}';
$string['stat_never'] = 'mai inviato';
$string['stat_since_last'] = 'Backup dall\'ultimo riepilogo';
$string['stat_totalsize'] = 'Spazio totale su disco occupato dai backup';
$string['stat_versions'] = 'Versioni di backup: {$a}';
$string['summary_email_fail_header'] = 'Corsi con backup non riuscito:';
$string['summary_email_loglink'] = 'Vedi log dettagliati: {$a}';
$string['summary_email_no_fail'] = 'Nessun backup fallito in questo periodo.';
$string['summary_email_since'] = 'Inizio periodo: {$a}';
$string['summary_email_subject'] = 'Moodle Time Machine: riepilogo giornaliero backup';
$string['summary_email_successes'] = 'Backup riusciti: {$a}';
$string['summary_email_totalmb'] = 'Dimensione totale (MB): {$a}';
$string['task_error_backup_courses'] = 'Errore fatale nel task pianificato di backup: {$a}';
$string['task_error_send_summary'] = 'Errore nel task di riepilogo: {$a}';
$string['task_error_single_course'] = 'Errore adhoc per il corso {$a->courseid}: {$a->message}';
$string['task_enforce_retention_error'] = 'Errore di retention per il corso {$a->courseid}: {$a->message}';
$string['task_enforce_retention_start'] = 'Applicazione retention per tutti i corsi (keep={$a})';
$string['task_backup_courses'] = 'Esegui backup Moodle Time Machine';
$string['task_send_summary'] = 'Invia email di riepilogo Moodle Time Machine';
$string['task_missing_courseid'] = 'Task adhoc senza courseid';
$string['timemachine:manage'] = 'Gestisci Moodle Time Machine';
$string['error_backup_path_outside'] = 'Percorso di backup non valido fuori dalla directory di archiviazione: {$a}';
$string['error_backup_write'] = 'Impossibile scrivere il backup in {$a}';
$string['error_create_storage'] = 'Impossibile creare la directory di archiviazione: {$a}';
