-- Copyright P.Christeas 2006


-- misc stuff for testing..


			
-- CREATE FUNCTION divide_time(div1 INTERVAL, div2 INTERVAL) RETURNS FLOAT AS $$
-- 	SELECT ( EXTRACT(EPOCH FROM $1) / EXTRACT(EPOCH FROM $2)) ;
-- 	$$ LANGUAGE SQL IMMUTABLE STRICT;
-- 
-- SELECT divide_time(INTERVAL '100sec', (endtime - starttime)) FROM cc_shopsessions;

/*SELECT date_trunc('sec', cc_shopsessions.starttime)AS session_start, 
	date_trunc('sec',(cc_shopsessions.endtime - cc_shopsessions.starttime)) AS session_time, 
	SUM(cc_call.stoptime - cc_call.starttime) AS sum_calls,
	to_char((divide_time(SUM(cc_call.stoptime - cc_call.starttime), (cc_shopsessions.endtime - cc_shopsessions.starttime)) * 100), '990D0000%') AS usage_pc
	FROM cc_call,cc_card, cc_shopsessions 
		WHERE cc_call.username = cc_card.username AND cc_shopsessions.card = cc_card.id
			AND cc_call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR cc_call.starttime <= cc_shopsessions.endtime)
		GROUP BY cc_shopsessions.id,cc_shopsessions.starttime, cc_shopsessions.endtime;*/
-- One view for all: have all the session transactions in one table.
	
CREATE OR REPLACE VIEW cc_agentcard_debt_v AS
	SELECT agentid, SUM(credit) as credit, 'Positive' AS typ  
		FROM cc_card,cc_agent_cards WHERE cc_agent_cards.card_id = cc_card.id
		AND cc_card.credit >0 GROUP BY cc_agent_cards.agentid
UNION
	SELECT agentid, SUM(credit) as credit, 'Negative' AS typ  
		FROM cc_card,cc_agent_cards WHERE cc_agent_cards.card_id = cc_card.id
		AND cc_card.credit <0 GROUP BY cc_agent_cards.agentid
UNION
	SELECT agentid, SUM(creditlimit) as credit, 'Limit' AS typ
		FROM cc_card,cc_agent_cards WHERE cc_agent_cards.card_id = cc_card.id
		GROUP BY cc_agent_cards.agentid;
		
 
/*CREATE OR REPLACE VIEW cc_texts_v AS
	SELECT id, txt AS txt_C FROM cc_texts AS t1 RIGHT OUTER JOIN cc_texts AS t2 ON t1.id = t2.id;*/

CREATE OR REPLACE VIEW cc_session_calls AS
SELECT cc_shopsessions.id AS sid,
	SUM(cc_call.stoptime - cc_call.starttime) AS duration, SUM(cc_call.sessionbill) AS bill,
	SUM(cc_call.buycost) AS buy_cost
	FROM cc_shopsessions, cc_call, cc_card
		WHERE cc_call.username = cc_card.username AND cc_shopsessions.card = cc_card.id
			AND cc_call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR cc_call.starttime <= cc_shopsessions.endtime)
		GROUP BY cc_shopsessions.id;

CREATE OR REPLACE VIEW cc_session_usage_v AS
SELECT  cc_shopsessions.id, cc_shopsessions.booth, cc_shopsessions.card,
	date_trunc('sec', cc_shopsessions.starttime) AS session_start, 
	date_trunc('sec',(cc_shopsessions.endtime - cc_shopsessions.starttime)) AS session_time, 
	calls.*,
	(divide_time(COALESCE(calls.duration,interval '0 min'), (cc_shopsessions.endtime - cc_shopsessions.starttime)) * 100) AS usage_pc
	FROM cc_shopsessions LEFT OUTER JOIN cc_session_calls AS calls ON cc_shopsessions.id = calls.sid
	 ;

SELECT booth, date_trunc('day',session_start) as dday,COUNT(id) AS sessions, COUNT(sid) AS sessions_act, SUM(session_time), AVG(session_time) AS session_time,
	SUM(duration) AS duration, SUM(bill) AS bill, AVG(usage_pc) AS usage
	FROM cc_session_usage_v
	GROUP BY booth, dday;
	
	
	
CREATE OR REPLACE VIEW cc_tariffrates_v3 AS SELECT cc_tariffgroup.id AS tg_id, cc_tariffgroup.tariffgroupname AS tg_name, 
	cc_tariffplan.id AS tp_id, cc_tariffplan.tariffname AS tp_name,
	cc_tariffplan.startingdate AS tp_start, cc_tariffplan.expirationdate AS tp_end,
	MIN(cc_ratecard.dialprefix) AS dialprefix, cc_ratecard.destination, MIN(cc_ratecard.rateinitial) AS rateinitial, 
	MIN(cc_ratecard.connectcharge + cc_ratecard.disconnectcharge) AS charge_once,
	MIN(cc_ratecard.billingblock) AS billingblock

	FROM cc_tariffgroup, cc_tariffgroup_plan, cc_tariffplan, cc_ratecard
	
	WHERE cc_tariffgroup.id = cc_tariffgroup_plan.idtariffgroup AND
		cc_tariffplan.id = cc_tariffgroup_plan.idtariffplan AND
		cc_ratecard.idtariffplan = cc_tariffplan.id
	GROUP BY cc_ratecard.destination,cc_tariffgroup.id, cc_tariffgroup.tariffgroupname, 
		cc_tariffplan.id,cc_tariffplan.tariffname,cc_tariffplan.startingdate, cc_tariffplan.expirationdate ;

CREATE OR REPLACE VIEW cc_tariffrates_v4 AS
SELECT cc_agent_cards.agentid, cc_ratecard.dialprefix, SUM(cc_call.sessiontime) AS total_secs, cc_call.id_ratecard, cc_ratecard.destination,
	cc_ratecard.rateinitial
	FROM cc_call,cc_agent_cards, cc_card, cc_ratecard
	WHERE (cc_call.starttime between NOW() - interval '10 days' AND NOW())
		AND cc_call.username = cc_card.username AND cc_agent_cards.card_id = cc_card.id
		AND cc_ratecard.id = cc_call.id_ratecard
	GROUP BY cc_call.id_ratecard, cc_agent_cards.agentid, cc_ratecard.destination,
		cc_ratecard.rateinitial, cc_ratecard.dialprefix
	ORDER BY SUM(cc_call.sessiontime) DESC LIMIT 100;
	
	
CREATE OR REPLACE VIEW cc_agent_calls_v AS
	SELECT starttime, stoptime, stoptime-starttime AS duration, terminatecause,
		sessionbill, id_ratecard,
		cc_agent_cards.agentid
	
	FROM cc_call, cc_card LEFT OUTER JOIN  cc_agent_cards ON cc_card.id = cc_agent_cards.card_id
	WHERE cc_card.username = cc_call.username;
	
	SELECT sum(duration), EXTRACT(hour from starttime) from cc_agent_calls_v 
		WHERE agentid = 1
		GROUP BY EXTRACT(hour from starttime)
		ORDER BY EXTRACT(hour from starttime);
		
CREATE OR REPLACE VIEW cc_agent_calls2_v AS
	SELECT cc_agent_calls_v.* , CASE WHEN terminatecause <> 'ANSWER' THEN terminatecause
		WHEN duration < interval '10 sec' THEN '10sec'
		WHEN duration < interval '30 sec' THEN '30sec'
		WHEN duration < interval '1 min' THEN '1min'
		WHEN duration > interval '5 min' THEN 'long'
		ELSE 'Normal'
		END AS categ,
		date_trunc('day',cc_agent_calls_v.starttime),
		EXTRACT(hour from cc_agent_calls_v.starttime),
		cc_ratecard.destination
		FROM cc_agent_calls_v LEFT OUTER JOIN cc_ratecard ON id_ratecard = cc_ratecard.id
		WHERE cc_agent_calls_v.starttime > '2007-06-13 11:22';
		

CREATE OR REPLACE VIEW cc_agent_calls3_v AS
	SELECT cc_agent_cards.agentid, starttime, stoptime-starttime AS duration, terminatecause,
		sessionbill, invoice_id,
		substring(calledstation from '#"%#"___' for '#') || '***' AS calledstation,
		CASE WHEN cc_agent.id IS NOT NULL THEN
			(sessionbill * (1 -cc_agent.commission))
			ELSE NULL END AS agentbill
		FROM cc_call, cc_card LEFT OUTER JOIN  cc_agent_cards ON cc_card.id = cc_agent_cards.card_id
			LEFT OUTER JOIN cc_agent ON cc_agent.id = cc_agent_cards.agentid
	WHERE cc_card.username = cc_call.username;
	
CREATE OR REPLACE VIEW cc_agent_calls4_v AS
	SELECT * FROM cc_agent_calls3_v WHERE
		agentid = 1 AND starttime >'2007-06-13 11:22' AND starttime < '2007-07-01 00:00'
		AND sessionbill > 0.0;


CREATE TABLE cc_numplan (
	id serial PRIMARY KEY,
	name VARCHAR(30) NOT NULL,
	intlen SMALLINT NOT NULL DEFAULT 5,
	intprefix VARCHAR(10) NOT NULL DEFAULT '55'
);

INSERT INTO cc_numplan(id,name) VALUES(1,'Default');

ALTER TABLE cc_card ADD numplan INTEGER not null DEFAULT 1;


/** Actually copy the ratecards: insert identical rates to the destination, as the source.
*/
CREATE OR REPLACE FUNCTION copy_ratecards(idtp_src integer, idtp_dest integer) RETURNS void AS $$
BEGIN

	INSERT INTO cc_ratecard(idtariffplan, dialprefix, destination, 
		buyrate, buyrateinitblock, buyrateincrement,
		rateinitial, initblock, billingblock, connectcharge, disconnectcharge,
		stepchargea, chargea, timechargea, billingblocka,
		stepchargeb, chargeb, timechargeb, billingblockb, 
		stepchargec, chargec, timechargec, billingblockc, 
		starttime, endtime, id_trunk, musiconhold)
	    SELECT $2,dialprefix, destination, 
		buyrate, buyrateinitblock, buyrateincrement,
		rateinitial, initblock, billingblock, connectcharge, disconnectcharge,
		stepchargea, chargea, timechargea, billingblocka,
		stepchargeb, chargeb, timechargeb, billingblockb, 
		stepchargec, chargec, timechargec, billingblockc, 
		starttime, endtime, id_trunk, musiconhold
	    FROM cc_ratecard WHERE idtariffplan = $1 ;
    
    -- NOT copied:  freetimetocall_package_offer, id_outbound_cidgroup, parent_card, startdate, stopdate
END; $$ LANGUAGE PLPGSQL STRICT;


CREATE OR REPLACE FUNCTION copy_ratecard_sell(rcid_src integer, rcid_dest integer) RETURNS void AS $$
	UPDATE cc_ratecard SET rateinitial = src.rateinitial, initblock = src.initblock,
		billingblock = src.billingblock, 
		connectcharge = src.connectcharge, disconnectcharge = src.disconnectcharge,
		stepchargea = src.stepchargea, chargea = src.chargea,
		timechargea = src.timechargea, billingblocka = src.billingblocka,
		stepchargeb = src.stepchargeb, chargeb = src.chargeb,
		timechargeb = src.timechargeb, billingblockb = src.billingblockb, 
		stepchargec = src.stepchargec, chargec = src.chargec,
		timechargec = src.timechargec, billingblockc = src.billingblockc
	    FROM cc_ratecard AS src WHERE src.id = $1 AND cc_ratecard.id = $2;

$$ LANGUAGE SQL STRICT VOLATILE;

ALTER TABLE cc_call ADD invoice_id BIGINT REFERENCES cc_invoices(id);
ALTER TABLE cc_charge ADD invoice_id BIGINT REFERENCES cc_invoices(id);

CREATE OR REPLACE FUNCTION agent_create_invoice(s_agentid BIGINT, s_startdate TIMESTAMP, s_stopdate TIMESTAMP) 
	RETURNS bigint AS $$
DECLARE
	sum_charges NUMERIC;
	sum_calls   NUMERIC;
	agent_vat   NUMERIC;
	sum_amount  NUMERIC;
	sum_tax     NUMERIC;
	ret_id      BIGINT;
BEGIN
	-- Step x: check for overlapping invoices
	PERFORM id FROM cc_invoices WHERE agentid = s_agentid 
		AND ( cover_startdate BETWEEN s_startdate AND s_stopdate
			OR cover_enddate BETWEEN s_startdate AND s_stopdate);
	IF FOUND THEN
		RAISE EXCEPTION 'Invoices already exist for this time period and agent';
	END IF;
	
	-- Step 1: check for unchecked charges
	-- TODO: we MISS charges on agent's cards w/o agentid in table.
	PERFORM id FROM cc_charge WHERE agentid = s_agentid AND
		(creationdate BETWEEN s_startdate AND s_stopdate)
		AND from_agent = true AND checked IS NULL;
	IF FOUND THEN
		RAISE EXCEPTION 'Unchecked charges found in that period';
	END IF;
	
	SELECT vat INTO agent_vat FROM cc_agent WHERE id = s_agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Can''t find VAT for agent %!',s_agentid;
	END IF;
	
	-- Create the invoice. We need its id this early.
	INSERT INTO cc_invoices(agentid, cover_startdate, cover_enddate )
		VALUES(s_agentid, s_startdate,s_stopdate)
		RETURNING id INTO ret_id;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Cannot create invoice';
	END IF;
	
	-- Step x: Sum calls and charges (here goes anything that should be invoiced)
	UPDATE cc_charge SET invoice_id = ret_id 
		WHERE agentid = s_agentid AND invoice_id IS NULL
		AND from_agent = true AND checked IS NOT NULL
		AND (creationdate BETWEEN s_startdate AND s_stopdate) ;
		
	-- FIXME: commission on charges? VAT on them? Non-invoiced charges?
	SELECT COALESCE(SUM(amount),0.0) INTO sum_charges FROM cc_charge WHERE invoice_id = ret_id;
	
	UPDATE cc_call SET invoice_id = ret_id FROM cc_card, cc_agent_cards
		WHERE cc_card.id = cc_agent_cards.card_id AND cc_agent_cards.agentid = s_agentid
		    AND cc_card.username = cc_call.username AND cc_call.invoice_id IS NULL
		    AND starttime BETWEEN s_startdate AND s_stopdate;

	-- That view subtracts the commission.
	SELECT COALESCE(sum(agentbill),0.0) INTO sum_calls FROM cc_agent_calls3_v
		WHERE invoice_id = ret_id;
	-- Create invoice.

	sum_amount := (sum_calls * (100.0 - agent_vat))/100.0 + sum_charges;
	sum_tax :=(sum_calls * agent_vat)/100.0 ;

	UPDATE cc_invoices SET amount = sum_amount, tax = sum_tax, total =sum_amount + sum_tax
		WHERE id = ret_id;
	RETURN ret_id;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;


SELECT agentid, cover_enddate + interval '0.01sec' FROM cc_invoices WHERE agentid IS NOT NULL;

CREATE OR REPLACE FUNCTION agent_create_all_invoices(s_agentid BIGINT, s_intv INTERVAL) RETURNS void AS $$
DECLARE
	s_time TIMESTAMP;
	e_time TIMESTAMP;
	s_trunc TEXT;
BEGIN
	SELECT MAX(cover_enddate) + interval '0.01 sec' INTO s_time FROM cc_invoices WHERE agentid = s_agentid;
	IF NOT FOUND OR s_time IS NULL THEN
		SELECT date_trunc('day',min(starttime)) INTO s_time FROM cc_agent_calls_v
			WHERE agentid = s_agentid;
		IF NOT FOUND OR s_time IS NULL THEN
			SELECT date_trunc('day',now() - s_intv) INTO s_time;
		END IF;
	END IF;
	
	RAISE NOTICE 'First date: %',s_time;
	s_trunc := CASE WHEN s_intv = interval '1 month' then 'month' 
		WHEN s_intv = interval '1 day' THEN 'day' ELSE 'month' END;
	LOOP
		e_time := date_trunc(s_trunc, s_time + s_intv) - interval '0.01 sec';
		IF e_time > now() THEN 
			EXIT;
		END IF;
		
		RAISE DEBUG 'Invoice from % to %',s_time, e_time;
		PERFORM agent_create_invoice(s_agentid, s_time, e_time);
		
		s_time := e_time + interval '0.01 sec';
	END LOOP;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;

CREATE OR REPLACE VIEW cc_agent_invoices_v AS
	SELECT cc_agent.login,cc_invoices.* , invoicesent_date, invoicestatus
		FROM cc_agent, cc_invoices LEFT JOIN (SELECT DISTINCT ON (invoiceid) * 
			FROM cc_invoice_history ORDER by invoiceid, invoicesent_date DESC) AS his 
			ON cc_invoices.id = his.invoiceid
		WHERE cc_agent.id = cc_invoices.agentid;

CREATE OR REPLACE FUNCTION cc_invoice_lock_f() RETURNS trigger AS $$
BEGIN
	IF OLD.invoice_id IS NOT NULL THEN
		RAISE EXCEPTION 'Call is invoiced in invoice %. Cannot modify',OLD.invoice_id;
	END IF;
	
	IF TG_OP = 'DELETE' THEN
		RETURN OLD;
	ELSE
		RETURN NEW;
	END IF;
END ; $$ LANGUAGE PLPGSQL;

CREATE TRIGGER cc_call_check_invoice BEFORE UPDATE OR DELETE ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

--	 (bill/EXTRACT(EPOCH FROM session_time))*3600
-- for percent: to_char('990D0000%')
--eof
