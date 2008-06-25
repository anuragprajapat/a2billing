--- Cards (customers) and Booths (reseller-points)

CREATE TABLE cc_agent (
    id serial NOT NULL PRIMARY KEY,
    name text NOT NULL,
    active boolean NOT NULL DEFAULT true,
    login VARCHAR(20) NOT NULL,
    passwd VARCHAR(40) NOT NULL,
    groupid integer,
    location text,
    datecreation timestamp without time zone DEFAULT now(),
    "language" text DEFAULT 'en'::text,
    tariffgroup integer REFERENCES cc_tariffgroup(id),
    options integer NOT NULL DEFAULT 0,
    credit NUMERIC(12,4) NOT NULL DEFAULT 0,
    climit NUMERIC(12,4) NOT NULL DEFAULT 0,
    currency CHARACTER(3) NOT NULL DEFAULT 'EUR',
    locale VARCHAR(10) DEFAULT 'C',
    commission NUMERIC(4,4),
    vat numeric(6,3) NOT NULL DEFAULT 0,
    email TEXT,
    banner TEXT
    );


CREATE TABLE cc_card_group (
    id serial NOT NULL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    agentid INTEGER REFERENCES cc_agent(id) ON DELETE RESTRICT,
    numplan INTEGER NOT NULL DEFAULT 1,
    uname_pattern TEXT NOT NULL DEFAULT '%#10',
    simultaccess integer DEFAULT 0,
    typepaid integer DEFAULT 0,
    tariffgroup integer REFERENCES cc_tariffgroup(id),
    def_currency VARCHAR(3) DEFAULT 'USD'::VARCHAR,
    voipcall integer DEFAULT 0,
    vat numeric(6,3) DEFAULT 0,
    initiallimit numeric(12,4) NOT NULL DEFAULT 0,
    invoiceday integer DEFAULT 1,
    expiretype INTEGER DEFAULT 0,
    expiredays INTEGER DEFAULT 0,
    autorefill INTEGER DEFAULT 0,
    agent_role INTEGER
);

-- removed fields:
--     enableexpire integer DEFAULT 0,
--     expiredays integer DEFAULT 0,
--     id_didgroup integer DEFAULT 0,
--     autorefill integer DEFAULT 0,
--     activatedbyuser boolean DEFAULT false NOT NULL
--     runservice integer DEFAULT 0,
-- 
--     sip_buddy integer DEFAULT 0,
--     iax_buddy integer DEFAULT 0,

CREATE TABLE cc_card (
    id bigserial PRIMARY KEY,
    grp integer NOT NULL REFERENCES cc_card_group(id),
    creationdate timestamp without time zone DEFAULT now(),
    firstusedate timestamp without time zone,
    expirationdate timestamp without time zone,
    username text NOT NULL UNIQUE,
    useralias text NOT NULL /*UNIQUE: not needed since numplan*/,
    userpass text NOT NULL,
    vm_password TEXT NOT NULL DEFAULT '1234',
--     uipass text,
    credit numeric(12,4) NOT NULL,
    status INT NOT NULL DEFAULT '1',
    lastname text,
    firstname text,
    address text,
    city text,
    state text,
    country text,
    zipcode text,
    phone text,
    email text,
    fax text,
    inuse integer DEFAULT 0,
    currency character varying(3) DEFAULT 'USD'::character varying,
    lastuse date DEFAULT now(),
    nbused integer DEFAULT 0,
    vm_tstamp TIMESTAMP NOT NULL DEFAULT now(),
    creditlimit NUMERIC(12,4) DEFAULT 0,
    "language" text DEFAULT 'en'::text,
    redial text,
    nbservice integer DEFAULT 0,
    id_campaign integer DEFAULT 0,
    num_trials_done integer DEFAULT 0,
    callback text,
    servicelastrun timestamp without time zone,
    loginkey text
);

-- ALTER TABLE cc_card DROP COLUMN userpass;

-- ALTER TABLE cc_card ADD COLUMN id_timezone INTEGER DEFAULT 0;

CREATE TABLE cc_booth (
	id serial NOT NULL PRIMARY KEY,
	name text NOT NULL,
	location text,
	agentid bigint NOT NULL REFERENCES cc_agent(id),
	datecreation timestamp without time zone DEFAULT now(),
	last_activation timestamp without time zone,
	disabled boolean NOT NULL DEFAULT 'f',
	cur_card_id bigint REFERENCES cc_card(id),
	def_card_id bigint REFERENCES cc_card(id),
	callerid TEXT,
	peername TEXT,
	peerpass TEXT
);


CREATE TABLE cc_status_log (
	id		BIGSERIAL PRIMARY KEY,
	status 	INT NOT NULL,
	id_cc_card INT NOT NULL,
	updated_date TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

-- ALTER TABLE cc_card ADD COLUMN tag CHAR(50);

-- ALTER TABLE cc_card ADD COLUMN template_invoice TEXT;
-- ALTER TABLE cc_card ADD COLUMN template_outstanding TEXT;


CREATE TABLE card_histories (
	id 		BIGSERIAL PRIMARY KEY,
	card_id 	BIGINT DEFAULT 0 NOT NULL,
	datecreation	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
	description 	TEXT
);



CREATE TABLE speeddials (
	id 		BIGSERIAL NOT NULL,
	card_id 	INT8 NOT NULL DEFAULT 0 REFERENCES cc_card(id),
	phone 		TEXT NOT NULL,
	name 		TEXT NOT NULL,
	speeddial 	INT4 DEFAULT 0,
	creationdate 	TIMESTAMP DEFAULT now()
);

GRANT SELECT ON cc_booth TO a2b_group;
GRANT SELECT ON cc_card TO a2b_group;
GRANT SELECT ON cc_card_group TO a2b_group;
GRANT SELECT ON speeddials TO a2b_group;

--eof
