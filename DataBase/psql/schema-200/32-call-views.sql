-- Views for calls

CREATE OR REPLACE VIEW cc_call_v AS
SELECT sessionid, uniqueid, cardid,nasipaddress,
    starttime,stoptime,
    sessiontime,calledstation,
    startdelay,stopdelay, attempt(la),srid(la),brid(la),tcause(la),hupcause(la),
    cause_ext(la),trunk(la),
    sessionbill,destination,tgid, qval, src, buycost
    FROM (SELECT sessionid, uniqueid, cardid,
		MAX(nasipaddress) AS nasipaddress ,
		MIN(starttime) AS starttime, MAX(stoptime) AS stoptime,
		SUM(sessiontime) AS sessiontime, MIN(calledstation) AS calledstation,
		SUM(startdelay) AS startdelay, SUM(stopdelay) AS stopdelay,
		last_attempt(ROW(attempt,srid, brid, tcause, hupcause, cause_ext, trunk)) AS la,
		SUM(sessionbill) as sessionbill, MAX(destination) AS destination, MAX(tgid) AS tgid,
		AVG(qval) AS qval, MAX(src) AS src, SUM(buycost) AS buycost
	FROM cc_call
	GROUP BY sessionid, uniqueid, cardid) AS foo ;
