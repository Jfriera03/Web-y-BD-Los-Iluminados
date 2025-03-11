INSERT INTO PAIS (codigoPais, nombrePais) VALUES
('ARG', 'Argentina'),
('BRA', 'Brasil'),
('CHL', 'Chile'),
('MEX', 'México'),
('USA', 'Estados Unidos'),
('ESP', 'España'),
('FRA', 'Francia'),
('ITA', 'Italia'),
('DEU', 'Alemania'),
('CAN', 'Canadá');


INSERT INTO CIUDAD (codigoCiudad, nombreCiudad, codigoPais) VALUES 
-- Argentina
('BUE', 'Buenos Aires', 'ARG'),
('COR', 'Córdoba', 'ARG'),
('ROS', 'Rosario', 'ARG'),
('MEN', 'Mendoza', 'ARG'),
('LPL', 'La Plata', 'ARG'),

-- Brasil
('SAO', 'São Paulo', 'BRA'),
('RIO', 'Río de Janeiro', 'BRA'),
('BSB', 'Brasilia', 'BRA'),
('SAL', 'Salvador', 'BRA'),
('BHZ', 'Belo Horizonte', 'BRA'),

-- Chile
('STG', 'Santiago', 'CHL'),
('VAL', 'Valparaíso', 'CHL'),
('CON', 'Concepción', 'CHL'),
('LSR', 'La Serena', 'CHL'),
('ANT', 'Antofagasta', 'CHL'),

-- México
('CDM', 'Ciudad de México', 'MEX'),
('GDL', 'Guadalajara', 'MEX'),
('MTY', 'Monterrey', 'MEX'),
('CCN', 'Cancún', 'MEX'),
('PUE', 'Puebla', 'MEX'),

-- Estados Unidos
('NYC', 'Nueva York', 'USA'),
('LAX', 'Los Ángeles', 'USA'),
('CHI', 'Chicago', 'USA'),
('MIA', 'Miami', 'USA'),
('HOU', 'Houston', 'USA'),

-- España
('MAD', 'Madrid', 'ESP'),
('BCN', 'Barcelona', 'ESP'),
('SVQ', 'Sevilla', 'ESP'),
('VLC', 'Valencia', 'ESP'),
('ZAR', 'Zaragoza', 'ESP'),

-- Francia
('PAR', 'París', 'FRA'),
('MAR', 'Marsella', 'FRA'),
('LYO', 'Lyon', 'FRA'),
('TOU', 'Toulouse', 'FRA'),
('NIZ', 'Niza', 'FRA'),

-- Italia
('ROM', 'Roma', 'ITA'),
('MIL', 'Milán', 'ITA'),
('NAP', 'Nápoles', 'ITA'),
('FLR', 'Florencia', 'ITA'),
('VNC', 'Venecia', 'ITA'),

-- Alemania
('BER', 'Berlín', 'DEU'),
('MUC', 'Múnich', 'DEU'),
('HAM', 'Hamburgo', 'DEU'),
('COL', 'Colonia', 'DEU'),
('FRA', 'Frankfurt', 'DEU'),

-- Canadá
('TOR', 'Toronto', 'CAN'),
('VAN', 'Vancouver', 'CAN'),
('MTL', 'Montreal', 'CAN'),
('OTT', 'Ottawa', 'CAN'),
('CAL', 'Calgary', 'CAN');


INSERT INTO EMPRESA (CIF, nombreEmpresa, emailEmpresa, telEmpresa, dirEmpresa, codigoCiudad) 
VALUES
-- Empresa Admin TotCloud
('Z87654321', 'Tot Cloud', 'administradores@totcloud.com', '976547689','Avenida Libertad 4', 'MAD' ),
-- Argentina
('A12345678', 'Comercio Argentino S.A.', 'contacto@comercioarg.com', '1145637890', 'Avenida 9 de Julio 1234', 'BUE'),
-- Brasil
('B98765432', 'Brasil Tech Ltda.', 'info@brasiltech.com', '1134567890', 'Rua São Paulo 567, São Paulo', 'SAO'),
-- Chile
('C23456789', 'Chile Innovador S.A.', 'ventas@chileinnovador.com', '2212345678', 'Avenida Libertador Bernardo O’Higgins 2345','STG'),
-- México
('M34567890', 'Soluciones Mexicanas S.A. de C.V.', 'contacto@solmex.com', '5551234567', 'Paseo de la Reforma 678', 'CDM'),
-- Estados Unidos
('U45678901', 'Global Enterprises Inc.', 'info@globalent.com', '2129876543', '5th Avenue 123', 'NYC'),
-- España
('E56789012', 'Tecnología Española S.L.', 'contacto@tecnoespaña.com', '913456789', 'Calle Gran Vía 45', 'MAD'),
('E67890123', 'Innovación España S.A.', 'info@innovacion.es', '917654321', 'Avenida Diagonal 678', 'BCN'),
-- Francia
('F78901234', 'Entreprises Françaises S.A.', 'contact@entreprisesfr.com', '0145678901', 'Rue de Paris 123', 'PAR'),
-- Italia
('I89012345', 'Imprese Italiane S.r.l.', 'info@impreseitaliane.it', '0641234567', 'Viale Roma 456', 'ROM'),
-- Alemania
('G90123456', 'Innovationen Deutschland GmbH', 'kontakt@innovationen.de', '0302345678', 'Berliner Strasse 789', 'BER'),
-- Canadá
('C01234567', 'Global Solutions Canada Inc.', 'contact@globalsolutions.ca', '4169876543', 'Bay Street 456', 'TOR');


INSERT INTO RED (ipPublica, tipo, CIF) 
VALUES 
-- Red para Tot Cloud
('200.189.80.87', 'Privada', 'Z87654321'), 
-- Argentina (Comercio Argentino S.A.)
('200.100.50.10', 'Privada', 'A12345678'),
-- Brasil (Brasil Tech Ltda.)
('200.200.60.20', 'Privada', 'B98765432'),
-- Chile (Chile Innovador S.A.)
('200.150.70.30', 'Privada', 'C23456789'),
-- México (Soluciones Mexicanas S.A. de C.V.)
('200.120.80.40', 'Privada', 'M34567890'),
-- Estados Unidos (Global Enterprises Inc.)
('200.110.90.50', 'Privada', 'U45678901'),
-- España (Tecnología Española S.L.)
('200.130.100.60', 'Privada', 'E56789012'),
-- España (Innovación España S.A.)
('200.130.100.70', 'Privada', 'E67890123'),
-- Francia (Entreprises Françaises S.A.)
('200.140.110.80', 'Privada', 'F78901234'),
-- Italia (Imprese Italiane S.r.l.)
('200.160.120.90', 'Privada', 'I89012345'),
-- Alemania (Innovationen Deutschland GmbH)
('200.170.130.100', 'Privada', 'G90123456'),
-- Canadá (Global Solutions Canada Inc.)
('200.180.140.110', 'Privada', 'C01234567');


INSERT INTO VLAN (idVLAN, nombreVLAN, ipPublica) 
VALUES 
-- VLAN de Tot Cloud
(1, 'VLAN TotCloud Nube', '200.189.80.87'),
(2, 'VLAN TotCloud Lluvia', '200.189.80.87'),
(3, 'VLAN TotCloud Tormenta', '200.189.80.87'),
-- Argentina (Comercio Argentino S.A.)
(4, 'VLAN_ComercioArg', '200.100.50.10'),
-- Brasil (Brasil Tech Ltda.)
(5, 'VLAN_BrasilTech', '200.200.60.20'),
-- Chile (Chile Innovador S.A.)
(6, 'VLAN_ChileInnovador', '200.150.70.30'),
-- México (Soluciones Mexicanas S.A. de C.V.)
(7, 'VLAN_SolucionesMex', '200.120.80.40'),
-- Estados Unidos (Global Enterprises Inc.)
(8, 'VLAN_GlobalEnt', '200.110.90.50'),
-- España (Tecnología Española S.L.)
(9, 'VLAN_TecnoEspaña', '200.130.100.60'),
-- España (Innovación España S.A.)
(10, 'VLAN_InnovacionEs', '200.130.100.70'),
-- Francia (Entreprises Françaises S.A.)
(11, 'VLAN_EntreprisesFR', '200.140.110.80'),
-- Italia (Imprese Italiane S.r.l.)
(12, 'VLAN_ImpreseIT', '200.160.120.90'),
-- Alemania (Innovationen Deutschland GmbH)
(13, 'VLAN_InnovationenDE', '200.170.130.100'),
-- Canadá (Global Solutions Canada Inc.)
(14, 'VLAN_GlobalSolutionsCA', '200.180.140.110');


INSERT INTO GRUPO (idGrupo, nombreGrupo) VALUES
(1, 'Administrador'),
(2, 'Usuario'),
(3, 'Invitado');

INSERT INTO PRIVILEGIO (idPrivilegio, nombrePrivilegio, descripcion) VALUES
(1, 'Dashboard_Admin', 'Acceso al panel de administrador'),
(2, 'Dashboard_User', 'Acceso al panel de usuario'),
(3, 'Editar_Productos', 'Permite editar productos'),
(4, 'Eliminar_Productos', 'Permite eliminar productos'),
(5, 'Editar_Usuarios', 'Permite editar usuarios'),
(6, 'Eliminar_Usuarios', 'Permite eliminar usuarios'),
(7, 'Editar_Permisos', 'Permite editar permisos'),
(8, 'Eliminar_Permisos', 'Permite eliminar permisos'),
(9, 'Comprar_Productos', 'Permite comprar productos');

INSERT INTO privilegiodegrupo (idGrupo, idPrivilegio) VALUES
-- Administradores
(1, 1),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),

-- Usuarios
(2, 2),
(2, 3),
(2, 4),
(2, 9),

-- Invitados
(3, 2);

INSERT INTO ETAPA (idEtapa, nombreEtapa) VALUES
(1, 'Desarrollo'),
(2, 'Mantenimiento'),
(3, 'Online'),
(4, 'Offline'),
(5, 'Alfa'),
(6, 'Beta');


INSERT INTO FABRICANTE (idFabricante, nombreFabricante) 
VALUES
(1, 'Intel'),
(2, 'AMD'),
(3, 'Samsung'),
(4, 'Seagate'),
(5, 'Kingston'),
(6, 'Corsair');


INSERT INTO SISTEMA_OPERATIVO (idSO, nombreSO) 
VALUES
(1, 'Windows'),
(2, 'Ubuntu'),
(3, 'Debian');


INSERT INTO DISTRIBUCION (idDistribucion, nombreDistribucion, idSO, precio) 
VALUES
(1, 'Windows 10 HOME', 1, 120),
(2, 'Windows 10 PRO', 1, 140),
(3, 'Windows 11 HOME', 1, 150),
(4, 'Windows 11 PRO', 1, 180),
(5, 'Windows Server 2022 Standard', 1, 500),
-- Ubuntu
(6, 'Ubuntu Desktop 20.04', 2, 50),
(7, 'Ubuntu Server 20.04', 2, 40),
(8, 'Ubuntu Desktop 22.04', 2, 70),
(9, 'Ubuntu Server 22.04', 2, 50),
-- Debian
(10, 'Debian 10 Stable', 3, 50),
(11, 'Debian 11 Bullseye', 3, 60),
(12, 'Debian 12 Bookworm', 3, 80);


INSERT INTO UNIDAD (unidadMedida) 
VALUES 
-- Velocidad de almacenamiento
('Mbps'),  
('Gbps'),      
('TBps'),  
-- Almacenamiento
('MB'),    
('GB'),   
('TB'),
('PB'),     
-- Frecuencia de CPU
('Hz'), 
('KHz'),   
('MHz'), 
('GHz'); 


INSERT INTO CPU (idCPU, modelo, numNucleos, frecuencia, precio, idFabricante, unidadFrecuencia) 
VALUES
-- Procesadores Intel
(1, 'Intel Core i5-11600K', 6, 3.90, 250.00, 1, 'GHz'),
(2, 'Intel Core i5-10400F', 6, 2.90, 180.00, 1, 'GHz'),
(3, 'Intel Core i3-10100', 4, 3.60, 120.00, 1, 'GHz'),
(4, 'Intel Core i9-10900K', 10, 3.70, 700.00, 1, 'GHz'),
(5, 'Intel Core i7-9700K', 8, 3.60, 400.00, 1, 'GHz'),
-- Procesadores AMD
(6, 'AMD Ryzen 5 5600X', 6, 3.70, 350.00, 2, 'GHz'),
(7, 'AMD Ryzen 7 3800X', 8, 3.90, 400.00, 2, 'GHz'),
(8, 'AMD Ryzen 5 3600', 6, 3.40, 200.00, 2, 'GHz'),
(9, 'AMD Ryzen 3 3100', 4, 3.60, 100.00, 2, 'GHz'),
(10, 'AMD Ryzen 9 3950X', 16, 3.50, 750.00, 2, 'GHz'),
(11, 'AMD Ryzen 7 2700X', 8, 3.70, 350.00, 2, 'GHz');


INSERT INTO RAM (idRAM, modelo, tipo, velocidad, capacidad, precio, idFabricante, unidadVelocidadRAM, unidadCapacidadRAM) 
VALUES
(1, 'Kingston HyperX Fury DDR4', 'DDR4', 3200.00, 16.00, 80.00, 5, 'MHz','MBps'),
(2, 'Corsair Vengeance LPX DDR4', 'DDR4', 3000.00, 16.00, 75.00, 6, 'MHz','MBps'),
(3, 'Corsair Vengeance LPX DDR4', 'DDR4', 3600.00, 32.00, 150.00, 6, 'MHz','MBps'),
(4, 'Kingston ValueRAM DDR4', 'DDR4', 2666.00, 8.00, 40.00, 5, 'MHz','MBps');


INSERT INTO ALMACENAMIENTO (idAlmacenamiento, nombreAlmacenamiento, tipo, capacidad, velocidad, precio, idFabricante, unidadCapacidad, unidadVelocidad) 
VALUES
-- Almacenamiento 256 GB
(1, 'Samsung 970 EVO', 'SSD', 256.00, 3500.00, 60.00, 3, 'GB', 'MBps'),
(2, 'Seagate Barracuda', 'HDD', 256.00, 150.00, 30.00, 4, 'GB', 'MBps'),
-- Almacenamiento 500 GB
(3, 'Samsung 860 EVO', 'SSD', 500.00, 550.00, 90.00, 3, 'GB', 'MBps'),
(4, 'Seagate IronWolf', 'HDD', 500.00, 200.00, 80.00, 4, 'GB', 'MBps'),
(5, 'Western Digital Blue', 'SSD', 500.00, 550.00, 85.00, 4, 'GB', 'MBps'),
-- Almacenamiento 1 TB
(6, 'Samsung 970 EVO Plus 1', 'SSD', 1.00, 3500.00, 150.00, 3, 'TB', 'MBps'),
(7, 'Seagate Barracuda', 'HDD', 1.00, 150.00, 40.00, 4, 'TB', 'MBps'),
(8, 'Western Digital Black', 'SSD', 1.00, 3200.00, 160.00, 4, 'TB', 'MBps'),
(9, 'SanDisk Ultra 3D', 'SSD', 1.00, 560.00, 120.00, 3, 'TB', 'MBps'),
-- Almacenamiento 4 TB
(10, 'Seagate IronWolf', 'HDD', 4.00, 220.00, 150.00, 4, 'TB', 'MBps'),
(11, 'Western Digital Red', 'HDD', 4.00, 180.00, 140.00, 4, 'TB', 'MBps'),
(12, 'Samsung 860 EVO', 'SSD', 4.00, 550.00, 350.00, 3, 'TB', 'MBps');

INSERT INTO LENGUAJE (nombreLenguaje, precioLenguaje) 
VALUES 
('Python', 49.99),
('Java', 59.99),
('JavaScript', 39.99),
('C++', 29.99),
('Ruby', 45.00),
('Go', 50.00),
('PHP', 19.99),
('Swift', 59.99),
('Kotlin', 54.99),
('Rust', 65.00),
('TypeScript', 44.99),
('C#', 49.00),
('R', 42.99),
('Perl', 33.99),
('Scala', 48.00);


INSERT INTO LIBRERIA (idLibreria, nombreLibreria, precioLibreria) 
VALUES 
(1, 'TensorFlow', 249.99),    -- Librería para Machine Learning
(2, 'NumPy', 0.00),           -- Librería para computación científica (open source)
(3, 'React', 0.00),           -- Librería para construir interfaces de usuario
(4, 'Django', 0.00),          -- Framework de alto nivel para Python (open source)
(5, 'Pandas', 0.00),          -- Librería para análisis de datos
(6, 'jQuery', 0.00),          -- Librería para facilitar la programación JavaScript
(7, 'Lodash', 45.00),         -- Librería de utilidades para JavaScript
(8, 'Spring', 99.99),         -- Framework para Java (desarrollo web y enterprise)
(9, 'Bootstrap', 0.00),       -- Librería para desarrollo front-end (CSS, JS)
(10, 'Flask', 0.00),          -- Framework de microservicios para Python
(11, 'Vue.js', 0.00),         -- Librería para interfaces de usuario
(12, 'Express.js', 0.00),     -- Framework web para Node.js
(13, 'Unittest', 0.00),       -- Librería de testing para Python
(14, 'Mockito', 79.99),       -- Librería para pruebas unitarias en Java
(15, 'Mongoose', 0.00);       -- Librería para interactuar con MongoDB en Node.js


INSERT INTO TBL_VERSION (numeroVersion) 
VALUES
-- Windows
(1022),  -- Windows 10 22H2 (Home)
(1023),  -- Windows 10 22H2 (Pro)
(1102),  -- Windows 11 22H2 (Home)
(1103),  -- Windows 11 22H2 (Pro)
(2022),  -- Windows Server 2022 (Standard)

-- Ubuntu
(2004),  -- Ubuntu 20.04 LTS (Desktop)
(2005),  -- Ubuntu 20.04 LTS (Server)
(2204),  -- Ubuntu 22.04 LTS (Desktop)
(2205),  -- Ubuntu 22.04 LTS (Server)

-- Debian
(1000), -- Debian 10 Buster
(1100), -- Debian 11 Bullseye
(1200), -- Debian 12 Bookworm

-- Versiones Git
(1), 
(2),  
(3),  
(4),
(5),
(6),
(17),
(18),
(22),
(23);


INSERT INTO VersionDeDistribucion (numeroVersion, idDistribucion) 
VALUES
-- Para Windows
(1022, 1), -- Windows 10 22H2 (Home)
(1023, 2), -- Windows 10 22H2 (Pro)
(1102, 3), -- Windows 11 22H2 (Home)
(1103, 4), -- Windows 11 22H2 (Pro)
(2022, 5), -- Windows Server 2022 (Standard)
-- Para Ubuntu
(2004, 6), -- Ubuntu 20.04 LTS (Desktop)
(2005, 7), -- Ubuntu 20.04 LTS (Server)
(2204, 8), -- Ubuntu 22.04 LTS (Desktop)
(2205, 9), -- Ubuntu 22.04 LTS (Server)
-- Para Debian
(1000, 10), -- Debian 10 Buster
(1100, 11), -- Debian 11 Bullseye
(1200, 12); -- Debian 12 Bookworm

INSERT INTO VersionDeLibreria (idLibreria, numeroVersion) 
VALUES 
-- TensorFlow
(1, 2),   -- Versión 2.x de TensorFlow
(1, 3),   -- Versión 3.x de TensorFlow
-- NumPy
(2, 1),   -- Versión 1.x de NumPy
(2, 2),   -- Versión 2.x de NumPy
-- React
(3, 17),  -- Versión 17.x de React
(3, 18),  -- Versión 18.x de React
-- Django
(4, 3),   -- Versión 3.x de Django
(4, 4),   -- Versión 4.x de Django
-- Pandas
(5, 1),   -- Versión 1.x de Pandas
(5, 2),   -- Versión 2.x de Pandas
-- jQuery
(6, 3),   -- Versión 3.x de jQuery
(6, 4),   -- Versión 4.x de jQuery
-- Lodash
(7, 4),   -- Versión 4.x de Lodash
(7, 5),   -- Versión 5.x de Lodash
-- Spring
(8, 5),   -- Versión 5.x de Spring
(8, 6),   -- Versión 6.x de Spring
-- Bootstrap
(9, 4),   -- Versión 4.x de Bootstrap
(9, 5),   -- Versión 5.x de Bootstrap
-- Flask
(10, 2),  -- Versión 2.x de Flask
(10, 3),  -- Versión 3.x de Flask
-- Vue.js
(11, 2),  -- Versión 2.x de Vue.js
(11, 3),  -- Versión 3.x de Vue.js
-- Express.js
(12, 4),  -- Versión 4.x de Express.js
(12, 5),  -- Versión 5.x de Express.js
-- Unittest
(13, 1),  -- Versión 1.x de Unittest
(13, 2),  -- Versión 2.x de Unittest
-- Mockito
(14, 3),  -- Versión 3.x de Mockito
(14, 4),  -- Versión 4.x de Mockito
-- Mongoose
(15, 5);  -- Versión 5.x de Mongoose

INSERT INTO VersionDeLenguaje (nombreLenguaje, numeroVersion)
VALUES 
-- Python

('Python', 2),     -- Versión de lenguaje (2)
('Python', 3),     -- Versión de lenguaje (3)
('Python', 4),     -- Versión de lenguaje (4)

-- Java
('Java', 17),
('Java', 18),
('Java', 22),
('Java', 23),

-- JavaScript
('JavaScript', 1),
('JavaScript', 2),
('JavaScript', 3),
('JavaScript', 4),
('JavaScript', 5),
('JavaScript', 6),
('JavaScript', 17),
('JavaScript', 18),

-- C++
('C++', 1),
('C++', 2),
('C++', 3),
('C++', 4),
('C++', 5),
('C++', 6),
('C++', 17),
('C++', 18),

-- Ruby
('Ruby', 1),
('Ruby', 2),
('Ruby', 3),
('Ruby', 4),
('Ruby', 5),
('Ruby', 6),
('Ruby', 17),
('Ruby', 18),

-- Go
('Go', 1),
('Go', 2),

-- PHP
('PHP', 1),
('PHP', 2),
('PHP', 3),
('PHP', 4),
('PHP', 5),
('PHP', 6),
('PHP', 17),
('PHP', 18),

-- Swift
('Swift', 1),
('Swift', 2),
('Swift', 3),
('Swift', 4),
('Swift', 5),
('Swift', 6),
('Swift', 17),
('Swift', 18),

-- Kotlin
('Kotlin', 1),
('Kotlin', 2),
('Kotlin', 3),
('Kotlin', 4),
('Kotlin', 5),
('Kotlin', 6),
('Kotlin', 17),
('Kotlin', 18),

-- Rust
('Rust', 1),
('Rust', 2),
('Rust', 3),
('Rust', 4),
('Rust', 5),
('Rust', 6),
('Rust', 17),
('Rust', 18),

-- TypeScript
('TypeScript', 1),
('TypeScript', 2),
('TypeScript', 3),
('TypeScript', 4),
('TypeScript', 5),
('TypeScript', 6),
('TypeScript', 17),
('TypeScript', 18),

-- C#
('C#', 1),
('C#', 2),
('C#', 3),
('C#', 4),
('C#', 5),
('C#', 6),
('C#', 17),
('C#', 18),

-- R
('R', 1),
('R', 2),
('R', 3),
('R', 4),
('R', 5),
('R', 6),
('R', 17),
('R', 18),

-- Perl
('Perl', 1),
('Perl', 2),
('Perl', 3),
('Perl', 4),
('Perl', 5),
('Perl', 6),
('Perl', 17),
('Perl', 18),

-- Scala
('Scala', 1),
('Scala', 2),
('Scala', 3),
('Scala', 4),
('Scala', 5),
('Scala', 6),
('Scala', 17),
('Scala', 18);


-- Inserts para la tabla SGBD
INSERT INTO SGBD (idSGBD, nombreSGBD, precio) VALUES 
(1, 'MySQL', 0.00),
(2, 'PostgreSQL', 0.00),
(3, 'Oracle', 1500.00),
(4, 'SQL Server', 1000.00);

-- Inserts para la tabla GIT
INSERT INTO GIT (idGit, tipoGit, precioGit) VALUES 
(1, 'GitHub', 0.00),
(2, 'GitLab', 0.00),
(3, 'Bitbucket', 0.00),
(4, 'Azure Repos', 0.00);


INSERT INTO VersionDeGit(idGit, numeroVersion) VALUES
-- GitHub
(1,1),
(1,2),
(1,3),
(1,4),
-- GitLab
(2,1),
(2,2),
(2,3),
(2,4),
-- Bitbucket
(3,1),
(3,2),
(3,3),
(3,4),
-- Azure Repos
(4,1),
(4,2),
(4,3),
(4,4);

INSERT INTO CAPACIDAD (nombreCapacidad) VALUES
(1),
(2),
(4),
(8),
(16),
(32),
(64),
(128),
(256),
(512),
(1024),
(2048),
(4096);

INSERT INTO capacidad_unidad (nombreCapacidad, unidadMedida,precio) VALUES
(1, 'GB', 1.00),
(2, 'GB', 2.00),
(4, 'GB', 3.00),
(8, 'GB', 4.00),
(16, 'GB', 5.00),
(32, 'GB', 6.00),
(64, 'GB', 7.00),
(128, 'GB', 8.00),
(256, 'GB', 9.00),
(512, 'GB', 10.00),
(1, 'TB', 11.00),
(2, 'TB', 12.00),
(4, 'TB', 13.00),
(8, 'TB', 14.00),
(16, 'TB', 15.00),
(32, 'TB', 16.00),
(64, 'TB', 17.00),
(128, 'TB', 18.00),
(256, 'TB', 19.00),
(512, 'TB', 20.00),
(1, 'PB', 21.00),
(2, 'PB', 22.00),
(4, 'PB', 23.00),
(8, 'PB', 24.00),
(16, 'PB', 25.00),
(32, 'PB', 26.00),
(64, 'PB', 27.00),
(128, 'PB', 28.00),
(256, 'PB', 29.00),
(512, 'PB', 30.00);

-- Productos iniciales que sirven para calcular el precio final de los futuros productos

INSERT INTO BD (idBD ,numConexiones,recuperarDatos ,controlAcceso ,idSGBD ,precioBase)VALUES
(1,1,1,1,1,100.00);

INSERT INTO VM (idVM ,idCPU ,idRAM , idAlmacenamiento ,idSO ,precioBase )VALUES 
(1,1,1,1,1,50.00);

INSERT INTO ENTORNO_DESAROLLO (idED,idGit ,precioBase )VALUES 
(1,1,20.00);

INSERT INTO ALMACENAMIENTO_CLOUD ( idAC , precioBase )VALUES 
(1,100.00);

DELIMITER //

CREATE PROCEDURE insertar_logs()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE idProducto INT;
    DECLARE capacidad_maxima DECIMAL(10, 2);
    DECLARE numConexiones INT;
    DECLARE almacenamiento_utilizado DECIMAL(10, 2);
    DECLARE porcentajeUsoCPU DECIMAL(5, 2);
    DECLARE porcentajeUsoRAM DECIMAL(5, 2);

    -- Cursor para VM
    DECLARE cur_vm CURSOR FOR 
        SELECT p.idProducto, a.capacidad
        FROM PRODUCTO p
        JOIN VM v ON p.idVM = v.idVM
        JOIN ALMACENAMIENTO a ON v.idAlmacenamiento = a.idAlmacenamiento;

    -- Cursor para Almacenamiento Cloud
    DECLARE cur_ac CURSOR FOR 
        SELECT p.idProducto, cc.nombreCapacidad
        FROM ALMACENAMIENTO_CLOUD ac
        JOIN capacidadcloud cc ON ac.idAC = cc.idAC
        JOIN CAPACIDAD c ON cc.nombreCapacidad = c.nombreCapacidad
        JOIN PRODUCTO p ON p.idAC = ac.idAC;

    -- Cursor para BD
    DECLARE cur_bd CURSOR FOR 
        SELECT p.idProducto, c.nombreCapacidad, bd.numConexiones
        FROM BD bd
        JOIN capacidadbd cb ON bd.idBD = cb.idBD
        JOIN CAPACIDAD c ON cb.nombreCapacidad = c.nombreCapacidad
        JOIN PRODUCTO p ON p.idBD = bd.idBD;

    -- Cursor para ED
    DECLARE cur_ed CURSOR FOR 
        SELECT p.idProducto
        FROM ENTORNO_DESAROLLO ed
        JOIN PRODUCTO p ON p.idED = ed.idED;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- Procesar VM
    OPEN cur_vm;
    read_loop_vm: LOOP
        FETCH cur_vm INTO idProducto, capacidad_maxima;
        IF done THEN
            SET done = 0;
            LEAVE read_loop_vm;
        END IF;

        SET almacenamiento_utilizado = ROUND(RAND() * capacidad_maxima, 2);
        SET almacenamiento_utilizado = LEAST(almacenamiento_utilizado, capacidad_maxima);
        SET porcentajeUsoCPU = LEAST(ROUND(RAND() * 99.99, 2), 99.99);
        SET porcentajeUsoRAM = LEAST(ROUND(RAND() * 99.99, 2), 99.99);

        INSERT INTO LOG_VM (timestamp, almacenamientoUtilizado, porcentajeUsoCPU, porcentajeUsoRAM, idProducto)
        VALUES (NOW(), almacenamiento_utilizado, porcentajeUsoCPU, porcentajeUsoRAM, idProducto);
    END LOOP;
    CLOSE cur_vm;

    -- Procesar Almacenamiento Cloud
    OPEN cur_ac;
    read_loop_ac: LOOP
        FETCH cur_ac INTO idProducto, capacidad_maxima;
        IF done THEN
            SET done = 0;
            LEAVE read_loop_ac;
        END IF;

        SET almacenamiento_utilizado = ROUND(RAND() * capacidad_maxima, 2);
        SET almacenamiento_utilizado = LEAST(almacenamiento_utilizado, capacidad_maxima);

        INSERT INTO log_almacenamiento_cloud (timestamp, almacenamientoUtilizado, idProducto)
        VALUES (NOW(), almacenamiento_utilizado, idProducto);
    END LOOP;
    CLOSE cur_ac;

    -- Procesar BD
    OPEN cur_bd;
    read_loop_bd: LOOP
        FETCH cur_bd INTO idProducto, capacidad_maxima, numConexiones;
        IF done THEN
            SET done = 0;
            LEAVE read_loop_bd;
        END IF;

        SET almacenamiento_utilizado = ROUND(RAND() * capacidad_maxima, 2);
        SET almacenamiento_utilizado = LEAST(almacenamiento_utilizado, capacidad_maxima);

        INSERT INTO log_bd (timestamp, almacenamientoUtilizado, numConexiones, idProducto)
        VALUES (NOW(), almacenamiento_utilizado, numConexiones, idProducto);
    END LOOP;
    CLOSE cur_bd;

    -- Procesar ED
    OPEN cur_ed;
    read_loop_ed: LOOP
        FETCH cur_ed INTO idProducto;
        IF done THEN
            SET done = 0;
            LEAVE read_loop_ed;
        END IF;

        INSERT INTO log_ed (timestamp, almacenamientoUtilizado, idProducto)
        VALUES (NOW(), NULL, idProducto);
    END LOOP;
    CLOSE cur_ed;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE hacer_admin(IN username VARCHAR(255))
BEGIN
    DECLARE idGrupoAdmin INT DEFAULT 1; -- Asume que el grupo de administradores tiene idGrupo = 1

    -- Actualizar el grupo del usuario a administrador
    UPDATE PerteneceGrupo SET idGrupo = idGrupoAdmin WHERE nickname = username;
END //

DELIMITER ;

-- Vista para usuarios y sus privilegios
CREATE VIEW UsuarioPrivilegio AS
SELECT 
    u.nickname AS usuario,
    p.nombrePrivilegio AS privilegio
FROM 
    USUARIO u
JOIN 
    PerteneceGrupo pg ON u.nickname = pg.nickname
JOIN 
    PrivilegioDeGrupo pdg ON pg.idGrupo = pdg.idGrupo
JOIN 
    PRIVILEGIO p ON pdg.idPrivilegio = p.idPrivilegio;


-- Evento para crear copia de seguridad diaria
SET GLOBAL event_scheduler="ON"
DELIMITER $$

CREATE EVENT IF NOT EXISTS InsertDataDaily
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    INSERT INTO COPIA_AC (precioBase, idCapacidad, idAC, idProducto, fechaCopia)
    SELECT precioBase, cc.nombreCapacidad, ac.idAC, idProducto, NOW()
    FROM almacenamiento_cloud ac
    JOIN capacidadcloud cc ON ac.idAC = cc.idAC
    JOIN producto p ON p.idAC = ac.idAC;
    

    INSERT INTO COPIA_BD (precioBase, numConexiones, recuperarDatos, controlAcceso, idSGBD, idBD, idProducto, idCapacidad, fechaCopia)
    SELECT precioBase, numConexiones, recuperarDatos, controlAcceso, idSGBD, bd.idBD, idProducto, cb.nombreCapacidad, NOW()
    FROM BD bd
    JOIN producto p ON p.idBD = bd.idBD
    JOIN capacidadbd cb ON bd.idBD = cb.idBD;
    

    INSERT INTO COPIA_ED (precioBase, idGit, idED, idProducto, fechaCopia)
    SELECT precioBase, idGit, ed.idED, idProducto, NOW()
    FROM entorno_desarollo ed
    JOIN producto p ON p.idED = ed.idED;


    INSERT INTO COPIA_VM (precioBase, idCPU, idRAM, idAlmacenamiento, idSO, idVM, idProducto, fechaCopia)
    SELECT precioBase, idCPU, idRAM, idAlmacenamiento, idSO, vm.idVM, idProducto, NOW()
    FROM VM vm
    JOIN producto p ON p.idVM = vm.idVM;

END$$

DELIMITER ;