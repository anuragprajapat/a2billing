-- Schema

\echo Creating schema..
CREATE SCHEMA a2b_old;

\echo Moving tables to a2b_old schema

ALTER TABLE cc_agent_cards SET SCHEMA a2b_old;
ALTER TABLE cc_agentpay SET SCHEMA a2b_old;
ALTER TABLE cc_agentrefill SET SCHEMA a2b_old;
ALTER TABLE cc_agent SET SCHEMA a2b_old;
ALTER TABLE cc_alarm_report SET SCHEMA a2b_old;
ALTER TABLE cc_alarm SET SCHEMA a2b_old;
ALTER TABLE cc_autorefill_report SET SCHEMA a2b_old;
ALTER TABLE cc_backup SET SCHEMA a2b_old;
ALTER TABLE cc_booth SET SCHEMA a2b_old;
ALTER TABLE cc_callback_spool SET SCHEMA a2b_old;
ALTER TABLE cc_callerid SET SCHEMA a2b_old;
ALTER TABLE cc_call SET SCHEMA a2b_old;
ALTER TABLE cc_campaign SET SCHEMA a2b_old;
ALTER TABLE cc_card_package_offer SET SCHEMA a2b_old;
ALTER TABLE cc_card SET SCHEMA a2b_old;
ALTER TABLE cc_charge_bk SET SCHEMA a2b_old;
ALTER TABLE cc_charge SET SCHEMA a2b_old;
ALTER TABLE cc_configuration SET SCHEMA a2b_old;
ALTER TABLE cc_country SET SCHEMA a2b_old;
ALTER TABLE cc_currencies SET SCHEMA a2b_old;
ALTER TABLE cc_did_destination SET SCHEMA a2b_old;
ALTER TABLE cc_didgroup SET SCHEMA a2b_old;
ALTER TABLE cc_did SET SCHEMA a2b_old;
ALTER TABLE cc_did_use SET SCHEMA a2b_old;
ALTER TABLE cc_ecommerce_product SET SCHEMA a2b_old;
ALTER TABLE cc_epayment_log SET SCHEMA a2b_old;
ALTER TABLE cc_iax_buddies SET SCHEMA a2b_old;
ALTER TABLE cc_invoice_history SET SCHEMA a2b_old;
ALTER TABLE cc_invoices SET SCHEMA a2b_old;
ALTER TABLE cc_logpayment SET SCHEMA a2b_old;
ALTER TABLE cc_logrefill SET SCHEMA a2b_old;
ALTER TABLE cc_outbound_cid_group SET SCHEMA a2b_old;
ALTER TABLE cc_outbound_cid_list SET SCHEMA a2b_old;
ALTER TABLE cc_package_offer SET SCHEMA a2b_old;
ALTER TABLE cc_payment_methods SET SCHEMA a2b_old;
ALTER TABLE cc_payments SET SCHEMA a2b_old;
ALTER TABLE cc_payments_status SET SCHEMA a2b_old;
ALTER TABLE cc_paypal SET SCHEMA a2b_old;
ALTER TABLE cc_paytypes SET SCHEMA a2b_old;
ALTER TABLE cc_phonelist SET SCHEMA a2b_old;
ALTER TABLE cc_provider SET SCHEMA a2b_old;
ALTER TABLE cc_ratecard SET SCHEMA a2b_old;
ALTER TABLE cc_server_group SET SCHEMA a2b_old;
ALTER TABLE cc_server_manager SET SCHEMA a2b_old;
ALTER TABLE cc_service_report SET SCHEMA a2b_old;
ALTER TABLE cc_service SET SCHEMA a2b_old;
ALTER TABLE cc_shopsessions SET SCHEMA a2b_old;
ALTER TABLE cc_sip_buddies SET SCHEMA a2b_old;
ALTER TABLE cc_speeddial SET SCHEMA a2b_old;
ALTER TABLE cc_subscription_fee SET SCHEMA a2b_old;
ALTER TABLE cc_tariffgroup_plan SET SCHEMA a2b_old;
ALTER TABLE cc_tariffgroup SET SCHEMA a2b_old;
ALTER TABLE cc_tariffplan SET SCHEMA a2b_old;
ALTER TABLE cc_templatemail SET SCHEMA a2b_old;
ALTER TABLE cc_texts SET SCHEMA a2b_old;
ALTER TABLE cc_trunk SET SCHEMA a2b_old;
ALTER TABLE cc_ui_authen SET SCHEMA a2b_old;
ALTER TABLE cc_voucher SET SCHEMA a2b_old;
