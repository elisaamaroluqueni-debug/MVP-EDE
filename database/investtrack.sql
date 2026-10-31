CREATE DATABASE IF NOT EXISTS investtrack_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE investtrack_db;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(150) NOT NULL UNIQUE,phone VARCHAR(30) NOT NULL,password_hash VARCHAR(255) NOT NULL,role ENUM('user','admin') NOT NULL DEFAULT 'user',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE financial_profiles(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL UNIQUE,savings DECIMAL(15,2) NOT NULL,capital DECIMAL(15,2) NOT NULL,goal ENUM('preservacao','crescimento','rendimento') NOT NULL,horizon ENUM('curto','medio','longo') NOT NULL,risk ENUM('conservador','moderado','agressivo') NOT NULL,
 CONSTRAINT chk_cap CHECK(capital>0 AND capital<=savings),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE brokers(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL UNIQUE,category ENUM('imobiliario','cripto','accoes') NOT NULL,min_investment DECIMAL(15,2) NOT NULL,fee_percent DECIMAL(5,2) NOT NULL,description VARCHAR(255),active TINYINT(1) NOT NULL DEFAULT 1,INDEX(category)) ENGINE=InnoDB;
CREATE TABLE assets(id INT AUTO_INCREMENT PRIMARY KEY,category ENUM('imobiliario','cripto','accoes') NOT NULL,name VARCHAR(120) NOT NULL,symbol VARCHAR(10),description VARCHAR(500),location VARCHAR(120),ref_price DECIMAL(18,2) NOT NULL,variation DECIMAL(6,2) NOT NULL DEFAULT 0,min_amount DECIMAL(15,2) NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,INDEX(category)) ENGINE=InnoDB;
CREATE TABLE investments(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,asset_id INT NOT NULL,broker_id INT NOT NULL,gross DECIMAL(15,2) NOT NULL,fee DECIMAL(15,2) NOT NULL,net DECIMAL(15,2) NOT NULL,quantity DECIMAL(24,8),current_value DECIMAL(15,2) NOT NULL,status ENUM('simulado','activo','concluido','cancelado') NOT NULL DEFAULT 'simulado',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id),FOREIGN KEY(asset_id) REFERENCES assets(id),FOREIGN KEY(broker_id) REFERENCES brokers(id),INDEX(user_id,status)) ENGINE=InnoDB;
-- Admin demonstrativo: admin@investtrack.ao / password  (alterar após o primeiro acesso)
INSERT INTO users(name,email,phone,password_hash,role) VALUES('Administrador','admin@investtrack.ao','900000000','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin');
INSERT INTO brokers(name,category,min_investment,fee_percent,description) VALUES
('Angola Property','imobiliario',250000,2.00,'Plataforma fictícia de oportunidades imobiliárias.'),
('ImobiCapital','imobiliario',500000,1.50,'Plataforma fictícia de participações imobiliárias.'),
('CryptoWay','cripto',10000,1.00,'Corretora fictícia de ativos digitais.'),
('DigitalCoin','cripto',15000,0.50,'Plataforma fictícia de negociação de criptomoedas.'),
('InvestPro','accoes',50000,1.00,'Corretora fictícia de acções.'),
('CapitalX','accoes',100000,0.75,'Plataforma fictícia de investimentos no mercado accionista.');
INSERT INTO assets(category,name,symbol,description,location,ref_price,variation,min_amount) VALUES
('imobiliario','Apartamento T3 Talatona',NULL,'Imóvel fictício para demonstração.','Talatona, Luanda',45000000,1.2,250000),
('imobiliario','Moradia V4 Benfica',NULL,'Imóvel fictício para demonstração.','Benfica, Luanda',80000000,0.8,500000),
('imobiliario','Escritório Maianga',NULL,'Imóvel fictício para demonstração.','Maianga, Luanda',30000000,-0.5,250000),
('cripto','Bitcoin','BTC','Valor demonstrativo, não é cotação real.',NULL,60000000,2.5,10000),
('cripto','Ethereum','ETH','Valor demonstrativo, não é cotação real.',NULL,3000000,-1.8,10000),
('cripto','Solana','SOL','Valor demonstrativo, não é cotação real.',NULL,150000,4.1,10000),
('accoes','Alfa Energia SA (fictícia)','ALFA','Empresa fictícia, não cotada em bolsa.',NULL,12000,0.9,50000),
('accoes','Beta Telecom SA (fictícia)','BETA','Empresa fictícia, não cotada em bolsa.',NULL,8500,-0.4,50000),
('accoes','Gama Agro SA (fictícia)','GAMA','Empresa fictícia, não cotada em bolsa.',NULL,5200,1.7,50000);
