cooperativapacifico	    cursalab_cpacifico
konecta	                cursalab_konecta
fisagroup	            cursalab_fisagroup
ecosac	                cursalab_ecosac
workplay	            cursalab_workplay


1	https://gestiona.demo.cursalab.pe      demo	                cursalab_demo
2	https://gestiona.claro.cursalab.pe     claro	            cursalab_claro
3	https://gestiona.agile.cursalab.pe     agile	            cursalab_startify
4	https://gestiona.especialista.cursalab.pe especialista	        cursalab_especialista
5	https://gestiona.campusaustralgroup.com
6	https://gestor.universidadcorporativafp.com.pe
7	https://demo.gestor.universidadcorporativafp.com.pe/
8	https://gestiona.potenciandotutalentongr.pe
9	https://gestiona.capacitacioncorporativagruposanpablo.com
10	https://gestiona.school.cursalab.pe         school	                cursalab_school
11	https://gestiona.inretail.cursalab.io
12	https://test.gestiona.inretail.cursalab.io
13	https://gestiona.essalud.cursalab.pe            essalud	                cursalab_essalud
14	https://gestiona.ecuacopia.cursalab.pe          ecuacopia	            cursalab_ecuacopia
15	https://gestiona.demo.cursalab.io

-- Agregar nuevas columnas
ALTER TABLE master_usuarios
ADD COLUMN username VARCHAR(255),
ADD COLUMN customer_id INT;
ADD COLUMN delete_at TIMESTAMP,

-- Eliminar columnas existentes
ALTER TABLE master_usuarios
DROP COLUMN empresa,
DROP COLUMN bd_fuente,
DROP COLUMN estado;

-- Agregar nuevas columnas a la tabla customers

ALTER TABLE customers
ADD COLUMN bd_fuente VARCHAR(255),
ADD COLUMN slug_empresa VARCHAR(255),
ADD COLUMN api_url VARCHAR(255),
ADD COLUMN estado BOOLEAN,
;
