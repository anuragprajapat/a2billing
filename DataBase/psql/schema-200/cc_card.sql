---

CREATE TABCE cc_card_group (
	id serial NOT NULL PRIMARY KEY,
    simultaccess integer DEFAULT 0,
    typepaid integer DEFAULT 0,
    def_currency character varying(3) DEFAULT 'USD'::character varying,*-*
    voipcall integer DEFAULT 0,
    vat numeric(6,3) DEFAULT 0,
    initialbalance numeric(12,4) NOT NULL DEFAULT 0,
    invoiceday integer DEFAULT 1,

);


CREATE TABLE cc_card (
    id bigserial NOT NULL PRIMARY KEY,
    grp integer NOT NULL REFERENCES cc_card_group(id),
    creationdate timestamp without time zone DEFAULT now(),
    firstusedate timestamp without time zone,
    expirationdate timestamp without time zone,
    enableexpire integer DEFAULT 0,
    expiredays integer DEFAULT 0,
    username text NOT NULL UNIQUE,
    useralias text NOT NULL UNIQUE,
    userpass text NOT NULL,
    uipass text,
    credit numeric(12,4) NOT NULL,
    tariff integer DEFAULT 0,
    id_didgroup integer DEFAULT 0,
    activated boolean DEFAULT false NOT NULL,
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
    creditlimit integer DEFAULT 0,*-*
    sip_buddy integer DEFAULT 0,
    iax_buddy integer DEFAULT 0,
    "language" text DEFAULT 'en'::text,
    redial text,
    runservice integer DEFAULT 0,
    nbservice integer DEFAULT 0,
    id_campaign integer DEFAULT 0,
    num_trials_done integer DEFAULT 0,
    callback text,
    servicelastrun timestamp without time zone,
    autorefill integer DEFAULT 0,
    loginkey text,
    activatedbyuser boolean DEFAULT false NOT NULL,
    id_subscription_fee INTEGER DEFAULT 0
);
