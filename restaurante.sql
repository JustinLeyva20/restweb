CREATE DATABASE IF NOT EXISTS restaurante;
USE restaurante;

CREATE TABLE config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ruc VARCHAR(15) NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  telefono VARCHAR(11) NOT NULL,
  direccion TEXT NOT NULL,
  mensaje VARCHAR(255) NOT NULL
);

CREATE TABLE salas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  mesas INT NOT NULL
);

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  correo VARCHAR(200) NOT NULL,
  pass VARCHAR(255) NOT NULL,
  rol VARCHAR(20) NOT NULL
);

CREATE TABLE platos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  fecha DATE DEFAULT NULL
);

CREATE TABLE bebidas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  fecha DATE DEFAULT NULL
);

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_sala INT NOT NULL,
  num_mesa INT NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('PENDIENTE','FINALIZADO','CANCELADO') DEFAULT 'PENDIENTE',
  usuario VARCHAR(100) NOT NULL,
  comentario TEXT DEFAULT NULL,
  FOREIGN KEY (id_sala) REFERENCES salas(id)
);

CREATE TABLE detalle_pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL,
  comentario TEXT DEFAULT NULL,
  id_pedido INT NOT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id)
);
CREATE TABLE reservas (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  id_sala    INT NOT NULL,
  num_mesa   INT NOT NULL,
  usuario    VARCHAR(100) NOT NULL,
  fecha      DATE NOT NULL,
  hora       TIME NOT NULL,
  personas   INT NOT NULL DEFAULT 1,
  nota       TEXT DEFAULT NULL,
  estado     ENUM('PENDIENTE','CONFIRMADA','CANCELADA') DEFAULT 'PENDIENTE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_sala) REFERENCES salas(id)
);
CREATE TABLE pedidos_web (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  usuario        VARCHAR(100) NOT NULL,
  direccion      TEXT NOT NULL,
  fecha          DATE NOT NULL,
  hora           TIME NOT NULL,
  metodo_pago    ENUM('EFECTIVO','YAPE','PLIN','TARJETA') NOT NULL,
  total          DECIMAL(10,2) NOT NULL,
  estado         ENUM('PENDIENTE','PREPARANDO','EN_CAMINO','ENTREGADO','CANCELADO') DEFAULT 'PENDIENTE',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE detalle_pedidos_web (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido   INT NOT NULL,
  nombre      VARCHAR(200) NOT NULL,
  precio      DECIMAL(10,2) NOT NULL,
  cantidad    INT NOT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos_web(id)
);


INSERT INTO config (ruc, nombre, telefono, direccion, mensaje)
VALUES ('65479877','Restaurante la Delicia','957847894','Lima - Perú','Gracias por su visita');

INSERT INTO salas (nombre, mesas)
VALUES ('SALA PRINCIPAL',15), ('SEGUNDO PISO',10);

INSERT INTO platos (nombre, precio, fecha)
VALUES 
('ARROZ CON POLLO',10.00,'2022-05-17'),
('CHAUFA',20.00,'2022-05-17'),
('GASEOSA COCA COLA 1.5 LITROS',8.00,'2022-05-17');

INSERT INTO bebidas (nombre, precio, fecha)
VALUES
('GASEOSA COCA COLA 1.5 LITROS',8.00,'2022-05-17');

INSERT INTO usuarios (nombre, correo, pass, rol)
VALUES ('Admin', 'admin@test.com', '123456', 'Administrador');

INSERT INTO pedidos (id_sala, num_mesa, total, estado, usuario)
VALUES 
(1,2,78.00,'FINALIZADO','Admin'),
(2,8,30.00,'PENDIENTE','Admin');

INSERT INTO detalle_pedidos (nombre, precio, cantidad, comentario, id_pedido)
VALUES 
('CHAUFA',20.00,1,'',1),
('ARROZ CON POLLO',10.00,5,'ARTO MAYONESA',1),
('GASEOSA COCA COLA 1.5 LITROS',8.00,1,'',1);

select * from platos;
select * from bebidas;
select * from reservas;

ALTER TABLE platos ADD COLUMN imagen VARCHAR(255) DEFAULT NULL;
ALTER TABLE platos ADD COLUMN imagen VARCHAR(255) DEFAULT NULL;
ALTER TABLE reservas ADD COLUMN motivo VARCHAR(255) DEFAULT NULL;
ALTER TABLE pedidos_web ADD COLUMN nombre_cliente VARCHAR(150) NOT NULL AFTER usuario;
ALTER TABLE pedidos_web ADD COLUMN telefono VARCHAR(15) NOT NULL AFTER nombre_cliente;
ALTER TABLE usuarios 
ADD COLUMN telefono VARCHAR(15) DEFAULT NULL,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
