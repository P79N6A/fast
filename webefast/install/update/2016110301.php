<?php
$u['bug_634'] = array(
    "DROP FUNCTION `f_spec1_init`;",
    "CREATE  FUNCTION `f_spec1_init`(p_spec_name varchar(50)) RETURNS varchar(100) CHARSET utf8
BEGIN
                #Routine body goes here...
        DECLARE spec1_str_code varchar(50) default '';
        DECLARE spec1_str_code_max varchar(50)  default '';
				DECLARE spec1_str_code_other varchar(50)  default '';

        select  `spec1_code` into spec1_str_code from `base_spec1` where `spec1_name` = p_spec_name limit 1;

        if spec1_str_code is null or spec1_str_code = ''
        then
                select max(spec1_code) into spec1_str_code_max from base_spec1 
        WHERE spec1_code LIKE '1%' or spec1_code LIKE '2%' or spec1_code LIKE '3%' or spec1_code LIKE '4%' or spec1_code LIKE '5%' 
or spec1_code LIKE '6%'  or spec1_code LIKE '7%'  or spec1_code LIKE '8%'  limit 1; 

                if spec1_str_code_max = '' or spec1_str_code_max is null
                then 
                        set spec1_str_code_max = 100;
                end if;

                set spec1_str_code = spec1_str_code_max + 1;

								select  `spec1_code` into spec1_str_code_other from `base_spec1` where `spec1_code` = spec1_str_code limit 1;

								WHILE spec1_str_code_other <> '' DO 

										SET spec1_str_code_other = '';

										set spec1_str_code = spec1_str_code + 1;
										
										select  `spec1_code` into spec1_str_code_other from `base_spec1` where `spec1_code` = spec1_str_code limit 1;

								END WHILE;

                INSERT INTO `base_spec1`  (`spec1_name`,`spec1_code`)  VALUES (p_spec_name,spec1_str_code);

        end if;

        RETURN spec1_str_code;

        END;",
    "DROP FUNCTION `f_spec2_init`;",
    "CREATE FUNCTION `f_spec2_init`(p_spec_name varchar(50)) RETURNS varchar(50) CHARSET utf8
BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';
				DECLARE spec_str_code_other varchar(50)  default '';


        select  `spec2_code` into spec_str_code from `base_spec2` where `spec2_name` = p_spec_name limit 1;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(spec2_code) into spec_str_code_max from base_spec2 
WHERE spec2_code LIKE '1%' or spec2_code like '2%'  or spec2_code like '3%' or spec2_code like '4%' or spec2_code like '5%' 
or spec2_code like '6%' or spec2_code like '7%' or spec2_code like '8%' limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

								select  `spec2_code` into spec_str_code_other from `base_spec2` where `spec2_code` = spec_str_code limit 1;

								WHILE spec_str_code_other <> '' DO 

										SET spec_str_code_other = '';

										set spec_str_code = spec_str_code + 1;
										
										select  `spec2_code` into spec_str_code_other from `base_spec2` where `spec2_code` = spec_str_code limit 1;

								END WHILE;

                INSERT INTO `base_spec2`  (`spec2_name`,`spec2_code`)  VALUES (p_spec_name,spec_str_code);

        end if;

        RETURN spec_str_code;
        END;",
    
);