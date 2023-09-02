
CREATE TABLE system_proccess_log (
                id INT AUTO_INCREMENT NOT NULL,
                system_user_id INT NOT NULL,
                system_proccess_log_id INT,
                start_time INT NOT NULL,
                end_time INT,
                class_name VARCHAR(100) NOT NULL,
                method_name VARCHAR(200) NOT NULL,
                PRIMARY KEY (id)
);


CREATE TABLE system_message_log (
                id INT AUTO_INCREMENT NOT NULL,
                system_proccess_log_id INT NOT NULL,
                message VARCHAR(500) NOT NULL,
                registry_type VARCHAR(8) NOT NULL,
                PRIMARY KEY (id)
);


ALTER TABLE system_proccess_log ADD CONSTRAINT system_user_system_proccess_log_fk
FOREIGN KEY (system_user_id)
REFERENCES system_user (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE system_proccess_log ADD CONSTRAINT system_proccess_log_system_proccess_log_fk
FOREIGN KEY (system_proccess_log_id)
REFERENCES system_proccess_log (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;


ALTER TABLE system_message_log ADD CONSTRAINT system_proccess_log_system_message_log_fk
FOREIGN KEY (system_proccess_log_id)
REFERENCES system_proccess_log (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;
