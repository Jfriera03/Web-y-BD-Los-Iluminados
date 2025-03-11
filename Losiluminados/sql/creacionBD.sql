CREATE DATABASE BD2Losiluminados;
USE BD2Losiluminados;

-- Tabla: LENGUAJE
CREATE TABLE LENGUAJE (
    nombreLenguaje VARCHAR(32) PRIMARY KEY,
    precioLenguaje DECIMAL(10,2) NOT NULL
);
-- Tabla: PAIS
CREATE TABLE PAIS (
    codigoPais CHAR(8) PRIMARY KEY,
    nombrePais VARCHAR(64) NOT NULL
);

-- Tabla: CIUDAD
CREATE TABLE CIUDAD (
    codigoCiudad CHAR(8) PRIMARY KEY,
    nombreCiudad VARCHAR(64) NOT NULL,
    codigoPais CHAR(8) NOT NULL,
    CONSTRAINT ciudadDePais FOREIGN KEY (codigoPais)
        REFERENCES PAIS(codigoPais)
);

-- Tabla: EMPRESA
CREATE TABLE EMPRESA (
    CIF CHAR(16) PRIMARY KEY,
    nombreEmpresa VARCHAR(64) NOT NULL,
    emailEmpresa VARCHAR(64) NOT NULL,
    telEmpresa CHAR(16) NOT NULL,
    dirEmpresa VARCHAR(128) NOT NULL,
    codigoCiudad CHAR(8) NOT NULL,
    CONSTRAINT empresaEnCiudad FOREIGN KEY (codigoCiudad)
        REFERENCES CIUDAD(codigoCiudad)
);

-- Tabla: RED
CREATE TABLE RED (
    ipPublica CHAR(16) PRIMARY KEY,
    tipo VARCHAR(32) NOT NULL,
    CIF CHAR(16) NOT NULL,
    CONSTRAINT redEmpresa FOREIGN KEY (CIF)
        REFERENCES EMPRESA(CIF));

-- Tabla: VLAN
CREATE TABLE VLAN (
    idVLAN INT PRIMARY KEY AUTO_INCREMENT,
    nombreVLAN VARCHAR(32) NOT NULL,
    ipPublica CHAR(16) NOT NULL,
    CONSTRAINT redVlan FOREIGN KEY (ipPublica)
        REFERENCES RED(ipPublica)
);

-- Tabla: USUARIO
CREATE TABLE USUARIO (
    nickname VARCHAR (16) PRIMARY KEY,
    nombreUsuario VARCHAR(64) NOT NULL,
    emailUsuario VARCHAR(64) NOT NULL,
    hashContraseña VARCHAR(256) NOT NULL,
    CIF CHAR(16) NOT NULL,
    CONSTRAINT pertenece FOREIGN KEY (CIF)
        REFERENCES EMPRESA(CIF)
);

-- Tabla: GRUPO
CREATE TABLE GRUPO (
    idGrupo INT PRIMARY KEY AUTO_INCREMENT,
    nombreGrupo VARCHAR(32) NOT NULL
);

-- Tabla: PRIVILEGIO
CREATE TABLE PRIVILEGIO (
    idPrivilegio INT PRIMARY KEY AUTO_INCREMENT,
    nombrePrivilegio VARCHAR(32) NOT NULL,
    descripcion TEXT NOT NULL
);

-- Tabla: ETAPA
CREATE TABLE ETAPA (
    idEtapa INT PRIMARY KEY,
    nombreEtapa VARCHAR(32) NOT NULL
);

-- Tabla: UNIDAD
CREATE TABLE UNIDAD (
    unidadMedida VARCHAR(16) PRIMARY KEY   
);

-- Tabla: PrivilegioDeGrupo
CREATE TABLE PrivilegioDeGrupo (
    idGrupo INT,
    idPrivilegio INT,
    PRIMARY KEY (idGrupo, idPrivilegio),
    CONSTRAINT GrupoConPriv FOREIGN KEY (idGrupo)
        REFERENCES GRUPO(idGrupo) ON DELETE CASCADE,
    CONSTRAINT privilegioAsignado FOREIGN KEY (idPrivilegio)
        REFERENCES PRIVILEGIO(idPrivilegio) ON DELETE CASCADE
);

-- Tabla: PerteneceGrupo
CREATE TABLE PerteneceGrupo (
    nickname VARCHAR(16),
    idGrupo INT,
    PRIMARY KEY (nickname, idGrupo),
    CONSTRAINT usuarioDeGrupo FOREIGN KEY (nickname)
        REFERENCES USUARIO(nickname) ON DELETE CASCADE,
    CONSTRAINT grupoConUser FOREIGN KEY (idGrupo)
        REFERENCES GRUPO(idGrupo) ON DELETE CASCADE
);

-- Tabla: SGBD
CREATE TABLE SGBD (
    idSGBD INT PRIMARY KEY AUTO_INCREMENT,
    nombreSGBD VARCHAR(32) NOT NULL,
    precio DECIMAL(10,2) NOT NULL
);

-- Tabla: BD
CREATE TABLE BD (
    idBD INT PRIMARY KEY AUTO_INCREMENT,
    numConexiones INT NOT NULL,
    recuperarDatos BOOLEAN NOT NULL,
    controlAcceso BOOLEAN NOT NULL,
    idSGBD INT NOT NULL,
    precioBase DECIMAL(10,2) NOT NULL,
    CONSTRAINT SGBDEBD FOREIGN KEY (idSGBD)
        REFERENCES SGBD(idSGBD) ON DELETE CASCADE
);

-- Tabla: FABRICANTE
CREATE TABLE FABRICANTE (
    idFabricante INT PRIMARY KEY AUTO_INCREMENT,
    nombreFabricante VARCHAR(64) NOT NULL
);

-- Tabla: CPU
CREATE TABLE CPU (
    idCPU INT PRIMARY KEY AUTO_INCREMENT,
    modelo VARCHAR(64) NOT NULL,
    numNucleos INT NOT NULL,
    frecuencia DECIMAL(10,2) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    idFabricante INT NOT NULL,
    unidadFrecuencia VARCHAR(16) NOT NULL,
    CONSTRAINT fabricanteCPU FOREIGN KEY (idFabricante)
        REFERENCES FABRICANTE(idFabricante) ON DELETE CASCADE,
    CONSTRAINT unidadFrecuenciaCPU FOREIGN KEY (unidadFrecuencia)
        REFERENCES UNIDAD(unidadMedida) ON DELETE CASCADE
);

-- Tabla: RAM
CREATE TABLE RAM (
    idRAM INT PRIMARY KEY AUTO_INCREMENT,
    modelo VARCHAR(64) NOT NULL,
    tipo VARCHAR(32) NOT NULL,
    velocidad DECIMAL(10,2) NOT NULL,
    capacidad DECIMAL(10,2) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    idFabricante INT NOT NULL,
    unidadVelocidadRAM VARCHAR(16) NOT NULL,
    unidadCapacidadRAM VARCHAR(16) NOT NULL,
    CONSTRAINT fabricanteRAM FOREIGN KEY (idFabricante)
        REFERENCES FABRICANTE(idFabricante) ON DELETE CASCADE,
    CONSTRAINT unidadVelRAM FOREIGN KEY (unidadVelocidadRAM)
        REFERENCES UNIDAD (unidadMedida) ON DELETE CASCADE,
    CONSTRAINT unidadCapacRAM FOREIGN KEY (unidadCapacidadRAM)
        REFERENCES UNIDAD (unidadMedida) ON DELETE CASCADE
);

-- Tabla: ALMACENAMIENTO
CREATE TABLE ALMACENAMIENTO (
    idAlmacenamiento INT PRIMARY KEY AUTO_INCREMENT,
    nombreAlmacenamiento VARCHAR(64) NOT NULL,
    tipo VARCHAR(32) NOT NULL,
    capacidad DECIMAL(10,2) NOT NULL,
    velocidad DECIMAL(10,2) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    idFabricante INT NOT NULL,
    unidadCapacidad VARCHAR(16) NOT NULL,
    unidadVelocidad VARCHAR(16) NOT NULL,
    CONSTRAINT fabricanteAlmacenamiento FOREIGN KEY (idFabricante)
        REFERENCES FABRICANTE(idFabricante) ON DELETE CASCADE,
    CONSTRAINT unidadCapacidadAlmacenamiento FOREIGN KEY (unidadCapacidad)
        REFERENCES UNIDAD(unidadMedida) ON DELETE CASCADE,
    CONSTRAINT unidadVelocidadAlmacenamiento FOREIGN KEY (unidadVelocidad)
        REFERENCES UNIDAD(unidadMedida) ON DELETE CASCADE
);

-- Tabla: SISTEMA_OPERATIVO
CREATE TABLE SISTEMA_OPERATIVO (
    idSO INT PRIMARY KEY AUTO_INCREMENT,
    nombreSO VARCHAR(32) NOT NULL
);

-- Tabla: DISTRIBUCION
CREATE TABLE DISTRIBUCION (
    idDistribucion INT PRIMARY KEY AUTO_INCREMENT,
    nombreDistribucion VARCHAR(32) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    idSO INT NOT NULL,
    CONSTRAINT distribucionSO FOREIGN KEY (idSO)
        REFERENCES SISTEMA_OPERATIVO(idSO) ON DELETE CASCADE
);

-- Tabla: GIT
CREATE TABLE GIT (
    idGit INT PRIMARY KEY AUTO_INCREMENT,
    tipoGit VARCHAR(64) NOT NULL,
    precioGit DECIMAL(10,2) NOT NULL
);

-- Tabla: VM
CREATE TABLE VM (
    idVM INT PRIMARY KEY AUTO_INCREMENT,
    idCPU INT NOT NULL,
    idRAM INT NOT NULL,
    idAlmacenamiento INT NOT NULL,
    idSO INT NOT NULL,
    precioBase DECIMAL(10,2) NOT NULL,
    CONSTRAINT CpuVM FOREIGN KEY (idCPU)
        REFERENCES CPU(idCPU) ON DELETE CASCADE,
    CONSTRAINT RamVM FOREIGN KEY (idRAM)
        REFERENCES RAM(idRAM) ON DELETE CASCADE,
    CONSTRAINT AlmacenamientoVM FOREIGN KEY (idAlmacenamiento)
        REFERENCES ALMACENAMIENTO(idAlmacenamiento) ON DELETE CASCADE,
    CONSTRAINT SOVM FOREIGN KEY (idSO)
        REFERENCES SISTEMA_OPERATIVO(idSO) ON DELETE CASCADE
);

-- Tabla: ENTORNO_DESAROLLO
CREATE TABLE ENTORNO_DESAROLLO (
    idED INT PRIMARY KEY AUTO_INCREMENT,
    idGit INT NOT NULL,
    precioBase DECIMAL(10,2) NOT NULL,
    CONSTRAINT EntornoGIT FOREIGN KEY (idGit)
        REFERENCES GIT(idGit) ON DELETE CASCADE
);

-- Tabla: TBL_VERSION
CREATE TABLE TBL_VERSION (
    numeroVersion INT PRIMARY KEY
);

-- Tabla: LIBRERIA
CREATE TABLE LIBRERIA (
    idLibreria INT PRIMARY KEY AUTO_INCREMENT,
    nombreLibreria VARCHAR(32) NOT NULL,
    precioLibreria DECIMAL(10,2) NOT NULL
);

--  Tabla capacidad
CREATE TABLE CAPACIDAD (
    nombreCapacidad INT PRIMARY KEY
);

CREATE TABLE CAPACIDAD_UNIDAD (
    nombreCapacidad INT,
    unidadMedida VARCHAR(16),
    precio DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (nombreCapacidad, unidadMedida),
    CONSTRAINT capacidadConUnidad FOREIGN KEY (nombreCapacidad)
        REFERENCES CAPACIDAD(nombreCapacidad) ON DELETE CASCADE,
    CONSTRAINT unidadDeCapacidad FOREIGN KEY (unidadMedida)
        REFERENCES UNIDAD(unidadMedida) ON DELETE CASCADE
);

-- Tabla: ALMACENAMIENTO_CLOUD
CREATE TABLE ALMACENAMIENTO_CLOUD (
    idAC INT PRIMARY KEY AUTO_INCREMENT,
    precioBase DECIMAL(10,2) NOT NULL
);

-- Tabla: LenguajeDeEntorno
CREATE TABLE LenguajeDeEntorno (
    idEntornoDesarrollo INT AUTO_INCREMENT,
    nombreLenguaje VARCHAR(32),
    PRIMARY KEY (idEntornoDesarrollo, nombreLenguaje),
    CONSTRAINT lenguajeDeEntorno FOREIGN KEY (idEntornoDesarrollo)
        REFERENCES ENTORNO_DESAROLLO(idED) ON DELETE CASCADE,
    CONSTRAINT EntornoConLenguaje FOREIGN KEY (nombreLenguaje)
        REFERENCES LENGUAJE(nombreLenguaje) ON DELETE CASCADE
);

-- Tabla: VersionDeLenguaje
CREATE TABLE VersionDeLenguaje (
    nombreLenguaje VARCHAR (32),
    numeroVersion INT,
    PRIMARY KEY (nombreLenguaje, numeroVersion),
    CONSTRAINT versionDelLenguaje FOREIGN KEY (nombreLenguaje)
        REFERENCES LENGUAJE(nombreLenguaje) ON DELETE CASCADE,
    CONSTRAINT lenguajeConVersion FOREIGN KEY (numeroVersion)
        REFERENCES TBL_VERSION (numeroVersion) ON DELETE CASCADE
);

-- Tabla: LibreriaDeEntorno
CREATE TABLE LibreriaDeEntorno (
    idEntornoDesarrollo INT,
    idLibreria INT,
    PRIMARY KEY (idEntornoDesarrollo, idLibreria),
    CONSTRAINT libreriaDeEntorno FOREIGN KEY (idEntornoDesarrollo)
        REFERENCES ENTORNO_DESAROLLO(idED) ON DELETE CASCADE,
    CONSTRAINT entornoConLibreria FOREIGN KEY (idLibreria)
        REFERENCES LIBRERIA(idLibreria) ON DELETE CASCADE
);

-- Tabla: VersionDeGit
CREATE TABLE VersionDeGit (
    idGit INT,
    numeroVersion INT,
    PRIMARY KEY (idGit, numeroVersion),
    CONSTRAINT versionDeGit FOREIGN KEY (idGit)
        REFERENCES GIT(idGit) ON DELETE CASCADE,
    CONSTRAINT gitConVersion FOREIGN KEY (numeroVersion)
        REFERENCES TBL_VERSION (numeroVersion) ON DELETE CASCADE
);
-- Tabla: VersionDeDistribucion
CREATE TABLE VersionDeDistribucion (
    numeroVersion INT,
    idDistribucion INT,
    PRIMARY KEY (numeroVersion, idDistribucion),
    CONSTRAINT distribucionConVersion FOREIGN KEY (numeroVersion)
        REFERENCES TBL_VERSION (numeroVersion) ON DELETE CASCADE,
    CONSTRAINT versionDeDistribucion FOREIGN KEY (idDistribucion)
        REFERENCES DISTRIBUCION(idDistribucion) ON DELETE CASCADE
);

-- Tabla: VersionDeLibreria
CREATE TABLE VersionDeLibreria (
    idLibreria INT,
    numeroVersion INT,
    PRIMARY KEY (idLibreria, numeroVersion),
    CONSTRAINT versionDeLibreria FOREIGN KEY (idLibreria)
        REFERENCES LIBRERIA(idLibreria) ON DELETE CASCADE,
    CONSTRAINT libreriaConVersion FOREIGN KEY (numeroVersion)
        REFERENCES TBL_VERSION (numeroVersion) ON DELETE CASCADE
);

-- Tabla: CapacidadBD
CREATE TABLE CapacidadBD (
    idBD INT ,
    nombreCapacidad INT NOT NULL,
    PRIMARY KEY(idBD,nombreCapacidad),
    CONSTRAINT cantidadBD FOREIGN KEY (idBD)
        REFERENCES BD(idBD) ON DELETE CASCADE,
    CONSTRAINT BDCapacidad FOREIGN KEY (nombreCapacidad)
        REFERENCES CAPACIDAD(nombreCapacidad) ON DELETE CASCADE
);

-- Tabla: CapacidadCloud
CREATE TABLE CapacidadCloud (
    nombreCapacidad INT NOT NULL,
    idAC INT ,
    PRIMARY KEY (idAC,nombreCapacidad),
    CONSTRAINT capCloud FOREIGN KEY (nombreCapacidad)
        REFERENCES CAPACIDAD(nombreCapacidad) ON DELETE CASCADE,
    CONSTRAINT cloudConCap FOREIGN KEY (idAC)
        REFERENCES ALMACENAMIENTO_CLOUD(idAC) ON DELETE CASCADE  
);

-- Tabla: PRODUCTO
CREATE TABLE PRODUCTO (
    idProducto INT PRIMARY KEY AUTO_INCREMENT,
    nombreProducto VARCHAR(32) NOT NULL,
    ipPrivada CHAR(16) NOT NULL,
    ipPublica CHAR(16) NOT NULL,
    codigoCiudad CHAR(8) NOT NULL,
    idVLAN INT NOT NULL,
    idEtapa INT NOT NULL,
    idBD INT,
    idVM INT,
    idED INT,
    idAC INT,
    CONSTRAINT seAloja FOREIGN KEY (codigoCiudad)
        REFERENCES CIUDAD(codigoCiudad) ON DELETE CASCADE,
    CONSTRAINT etapa FOREIGN KEY (idEtapa)
        REFERENCES ETAPA(idEtapa) ON DELETE CASCADE,
    CONSTRAINT entornoDeDesarrollo FOREIGN KEY (idED)
        REFERENCES ENTORNO_DESAROLLO (idED) ON DELETE CASCADE,
    CONSTRAINT almacenamientoCloud FOREIGN KEY (idAC)
        REFERENCES ALMACENAMIENTO_CLOUD (idAC) ON DELETE CASCADE,
    CONSTRAINT VLANDeProducto FOREIGN KEY (idVLAN)
        REFERENCES VLAN(idVLAN),
    CONSTRAINT baseDeDatos FOREIGN KEY (idBD)
        REFERENCES BD (idBD) ON DELETE CASCADE,
    CONSTRAINT maquinaVirtual FOREIGN KEY (idVM)
        REFERENCES VM (idVM) ON DELETE CASCADE
);

-- Tabla: LOG_BD
CREATE TABLE LOG_BD (
    idLOG_BD INT PRIMARY KEY AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    almacenamientoUtilizado DECIMAL(10,2) NOT NULL,
    numConexiones INT NOT NULL,
    idProducto INT NOT NULL,
    CONSTRAINT monitorizarLOGBD FOREIGN KEY (idProducto)
        REFERENCES PRODUCTO(idProducto) ON DELETE CASCADE
);

-- Tabla: LOG_VM
CREATE TABLE LOG_VM (
    idLOG_VM INT PRIMARY KEY AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    almacenamientoUtilizado DECIMAL(10,2) NOT NULL,
    porcentajeUsoCPU DECIMAL(6,2) NOT NULL,
    porcentajeUsoRAM DECIMAL(6,2) NOT NULL,
    idProducto INT NOT NULL,
    CONSTRAINT monitorizarLOGVM FOREIGN KEY (idProducto)
        REFERENCES PRODUCTO(idProducto) ON DELETE CASCADE
);

-- Tabla: LOG_ED
CREATE TABLE LOG_ED (
    idLOG_ED INT PRIMARY KEY AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    almacenamientoUtilizado DECIMAL(10,2),
    idProducto INT NOT NULL,
    CONSTRAINT monitorizarLOGED FOREIGN KEY (idProducto)
        REFERENCES PRODUCTO(idProducto) ON DELETE CASCADE
);

-- Tabla: LOG_ALMACENAMIENTO_CLOUD
CREATE TABLE LOG_ALMACENAMIENTO_CLOUD (
    idLOG_AC INT PRIMARY KEY AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    almacenamientoUtilizado DECIMAL(10,2) NOT NULL,
    idProducto INT NOT NULL,
    CONSTRAINT monitorizarLOGAC FOREIGN KEY (idProducto)
        REFERENCES PRODUCTO(idProducto) ON DELETE CASCADE
);

-- Tabla: PEDIDO
CREATE TABLE PEDIDO (
    idPedido INT PRIMARY KEY AUTO_INCREMENT,
    fechaPedido DATETIME NOT NULL,
    nickname VARCHAR(16) NOT NULL,
    idProducto INT NOT NULL,
    CONSTRAINT realiza FOREIGN KEY (nickname)
        REFERENCES USUARIO(nickname)  ON DELETE CASCADE,
    CONSTRAINT contiene FOREIGN KEY (idProducto)
        REFERENCES PRODUCTO(idProducto) ON DELETE CASCADE
);

-- Tabla: COPIA_VM
CREATE TABLE COPIA_VM (
    idCopiaVM INT PRIMARY KEY AUTO_INCREMENT,
    precioBase DECIMAL(10,2) NOT NULL,
    idCPU INT NOT NULL,
    idRAM INT NOT NULL,
    idAlmacenamiento INT NOT NULL,
    idSO INT NOT NULL,
    idVM INT NOT NULL,
    idProducto INT NOT NULL,
    fechaCopia DATETIME NOT NULL
);

-- Tabla: COPIA_BD
CREATE TABLE COPIA_BD (
    idCopiaBD INT PRIMARY KEY AUTO_INCREMENT,
    precioBase DECIMAL(10,2) NOT NULL,
    numConexiones INT NOT NULL,
    recuperarDatos BOOLEAN NOT NULL,
    controlAcceso BOOLEAN NOT NULL,
    idSGBD INT NOT NULL,
    idCapacidad INT NOT NULL,
    idBD INT NOT NULL,
    idProducto INT NOT NULL,
    fechaCopia DATETIME NOT NULL
);

-- Tabla: COPIA_ED
CREATE TABLE COPIA_ED (
    precioBase DECIMAL(10,2) NOT NULL,
    idCopiaED INT PRIMARY KEY AUTO_INCREMENT,
    idGit INT NOT NULL,
    idED INT NOT NULL,
    idProducto INT NOT NULL,
    fechaCopia DATETIME NOT NULL
);

-- Tabla: COPIA_AC
CREATE TABLE COPIA_AC (
    idCopiaAC INT PRIMARY KEY AUTO_INCREMENT,
    precioBase DECIMAL(10,2) NOT NULL,
    idCapacidad INT NOT NULL,
    idAC INT NOT NULL,
    idProducto INT NOT NULL,
    fechaCopia DATETIME NOT NULL
);


-- Índice del nombre producto de la tabla PRODUCTO
CREATE INDEX idx_nombreProducto ON PRODUCTO (nombreProducto);
