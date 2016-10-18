--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: users; Type: TABLE; Schema: public; Owner: cartman; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    fullname character varying(100) NOT NULL,
    password character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    email_public character varying(100) NOT NULL,
    accesslevel smallint DEFAULT 0 NOT NULL,
    create_time integer DEFAULT 0 NOT NULL,
    create_ip character varying(40) NOT NULL,
    activation_time integer DEFAULT 0 NOT NULL,
    activation_ip character varying(40),
    login_time integer DEFAULT 0 NOT NULL,
    login_ip character varying(40),
    activity_time integer DEFAULT 0 NOT NULL,
    activity_ip character varying(40),
    status character varying(20) NOT NULL
);


ALTER TABLE public.users OWNER TO orpheus;

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


-- ALTER TABLE public.users_id_seq OWNER TO orpheus;

ALTER SEQUENCE users_id_seq OWNED BY users.id;

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);

-- GRANT ALL ON TABLE users TO orpheus;

-- GRANT ALL ON SEQUENCE users_id_seq TO orpheus;
