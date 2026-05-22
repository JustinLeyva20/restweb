-- PostgreSQL migration for restaurante

CREATE TABLE IF NOT EXISTS config (
  id SERIAL PRIMARY KEY,
  ruc VARCHAR(15) NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  telefono VARCHAR(11) NOT NULL,
  direccion TEXT NOT NULL,
  mensaje VARCHAR(255) NOT NULL,
  horario_apertura VARCHAR(10) NOT NULL DEFAULT '08:00',
  horario_cierre VARCHAR(10) NOT NULL DEFAULT '20:00'
);

CREATE TABLE IF NOT EXISTS salas (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  mesas INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  correo VARCHAR(200) NOT NULL,
  pass VARCHAR(255) NOT NULL,
  rol VARCHAR(20) NOT NULL,
  telefono VARCHAR(15) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  direccion VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS platos (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  fecha DATE DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS bebidas (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  fecha DATE DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS postres (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  fecha DATE DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS reportes (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL,
  correo VARCHAR(150) DEFAULT NULL,
  asunto VARCHAR(200) NOT NULL,
  descripcion TEXT NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT reportes_estado_check CHECK (estado IN ('PENDIENTE','REVISADO','RESUELTO'))
);

CREATE TABLE IF NOT EXISTS pedidos (
  id SERIAL PRIMARY KEY,
  id_sala INTEGER NOT NULL,
  num_mesa INTEGER NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(10,2) NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
  usuario VARCHAR(100) NOT NULL,
  comentario TEXT DEFAULT NULL,
  CONSTRAINT pedidos_estado_check CHECK (estado IN ('PENDIENTE','FINALIZADO','CANCELADO')),
  FOREIGN KEY (id_sala) REFERENCES salas(id)
);

CREATE TABLE IF NOT EXISTS detalle_pedidos (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  cantidad INTEGER NOT NULL,
  comentario TEXT DEFAULT NULL,
  id_pedido INTEGER NOT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id)
);

CREATE TABLE IF NOT EXISTS reservas (
  id SERIAL PRIMARY KEY,
  id_sala INTEGER NOT NULL,
  num_mesa INTEGER NOT NULL,
  usuario VARCHAR(100) NOT NULL,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  personas INTEGER NOT NULL DEFAULT 1,
  nota TEXT DEFAULT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  motivo VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (id_sala) REFERENCES salas(id),
  CONSTRAINT reservas_estado_check CHECK (estado IN ('PENDIENTE','CONFIRMADA','CANCELADA'))
);

CREATE TABLE IF NOT EXISTS pedidos_web (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL,
  nombre_cliente VARCHAR(150) NOT NULL,
  telefono VARCHAR(15) NOT NULL,
  direccion TEXT NOT NULL,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  metodo_pago VARCHAR(20) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT pedidos_web_pago_check CHECK (metodo_pago IN ('EFECTIVO','YAPE','PLIN','TARJETA')),
  CONSTRAINT pedidos_web_estado_check CHECK (estado IN ('PENDIENTE','PREPARANDO','EN_CAMINO','ENTREGADO','CANCELADO'))
);

CREATE TABLE IF NOT EXISTS detalle_pedidos_web (
  id SERIAL PRIMARY KEY,
  id_pedido INTEGER NOT NULL,
  nombre VARCHAR(200) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  cantidad INTEGER NOT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos_web(id)
);

-- Data
INSERT INTO config (ruc, nombre, telefono, direccion, mensaje)
VALUES ('65479877','Restaurante la Delicia','957847894','Lima - Perú','Gracias por su visita');

INSERT INTO salas (nombre, mesas)
VALUES ('SALA PRINCIPAL',15), ('SEGUNDO PISO',10);

INSERT INTO platos (nombre, precio, imagen, fecha)
VALUES
('ARROZ CHAUFA',18.00,'chaufa.jpg',CURRENT_DATE),
('LOMO SALTADO',24.00,'lomo_saltado.jpg',CURRENT_DATE),
('AJI DE GALLINA',20.00,'aji_de_gallina.jpg',CURRENT_DATE),
('CEVICHE CLASICO',28.00,'ceviche.jpg',CURRENT_DATE),
('ARROZ CON POLLO',17.00,'arroz_con_pollo.jpg',CURRENT_DATE);

INSERT INTO bebidas (nombre, precio, imagen, fecha)
VALUES
('CHICHA MORADA',8.00,'chicha_morada.jpg',CURRENT_DATE),
('EMOLIENTE',6.00,'emoliente.jpg',CURRENT_DATE),
('INCA KOLA',7.00,'inca_kola.jpg',CURRENT_DATE),
('JUGO DE MARACUYA',9.00,'jugo_maracuya.jpg',CURRENT_DATE),
('CHICHA DE JORA',10.00,'chicha_de_jora.jpg',CURRENT_DATE);

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

postgresql://neondb_owner:npg_xs9OYRo8eltd@ep-blue-firefly-ajwhbjh6-pooler.c-3.us-east-2.aws.neon.tech/neondb?channel_binding=require&sslmode=require