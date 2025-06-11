-- Création des rôles
CREATE TABLE role (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL
);

-- Création des utilisateurs
CREATE TABLE user (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255),
    user_type ENUM('student', 'parent', 'marketing_member') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES role(role_id)
);

-- Création des campus
CREATE TABLE campus (
    campus_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    city VARCHAR(100)
);

-- Création des journées portes ouvertes
CREATE TABLE open_day (
    jpo_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    date DATE,
    max_capacity INT,
    campus_id INT NOT NULL,
    FOREIGN KEY (campus_id) REFERENCES campus(campus_id)
);

-- Création des inscriptions
CREATE TABLE registration (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jpo_id INT NOT NULL,
    registration_date DATETIME,
    status ENUM('registered', 'unregistered') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (jpo_id) REFERENCES open_day(jpo_id)
);

-- Création des commentaires
CREATE TABLE comment (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jpo_id INT NOT NULL,
    content TEXT,
    comment_date DATETIME,
    moderator_reply TEXT,
    reply_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (jpo_id) REFERENCES open_day(jpo_id)
);

-- Insertion des rôles de base
INSERT INTO role (role_name) VALUES ('élève'), ('admin');
