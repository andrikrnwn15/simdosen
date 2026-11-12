-- Database: simdosen
-- Hosting (InfinityFree): import ke database yang sudah dibuat di panel (tanpa CREATE DATABASE).
-- Lokal (XAMPP): jalankan dulu:  CREATE DATABASE simdosen CHARACTER SET utf8mb4;  USE simdosen;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS dosen, users, roles, jabatan_fungsional, kepangkatan, pendidikan, rumpun_ilmu, status_pegawai, ikatan_kerja, status_aktivitas, penempatan;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(30) NOT NULL UNIQUE,
  keterangan VARCHAR(150)
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  role_id INT NOT NULL,
  api_token VARCHAR(64) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE jabatan_fungsional (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL) ENGINE=InnoDB;
CREATE TABLE kepangkatan        (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL) ENGINE=InnoDB;
CREATE TABLE pendidikan         (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL) ENGINE=InnoDB;
CREATE TABLE rumpun_ilmu        (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(100) NOT NULL) ENGINE=InnoDB;
CREATE TABLE status_pegawai     (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL) ENGINE=InnoDB;
CREATE TABLE ikatan_kerja       (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL) ENGINE=InnoDB;
CREATE TABLE status_aktivitas   (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL) ENGINE=InnoDB;
CREATE TABLE penempatan         (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(120) NOT NULL) ENGINE=InnoDB;

CREATE TABLE dosen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_registrasi VARCHAR(30) NOT NULL UNIQUE,
  nuptk VARCHAR(30),
  nama VARCHAR(150) NOT NULL,
  nip VARCHAR(30),
  jabatan_id INT NULL,
  pangkat_id INT NULL,
  pendidikan_id INT NULL,
  rumpun_id INT NULL,
  status_serdos ENUM('Sudah','Belum') NOT NULL DEFAULT 'Belum',
  status_pegawai_id INT NULL,
  ikatan_kerja_id INT NULL,
  status_aktivitas_id INT NULL,
  penempatan_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (jabatan_id) REFERENCES jabatan_fungsional(id),
  FOREIGN KEY (pangkat_id) REFERENCES kepangkatan(id),
  FOREIGN KEY (pendidikan_id) REFERENCES pendidikan(id),
  FOREIGN KEY (rumpun_id) REFERENCES rumpun_ilmu(id),
  FOREIGN KEY (status_pegawai_id) REFERENCES status_pegawai(id),
  FOREIGN KEY (ikatan_kerja_id) REFERENCES ikatan_kerja(id),
  FOREIGN KEY (status_aktivitas_id) REFERENCES status_aktivitas(id),
  FOREIGN KEY (penempatan_id) REFERENCES penempatan(id)
) ENGINE=InnoDB;

INSERT INTO roles (nama,keterangan) VALUES
('admin','Akses penuh: dosen, master data, pengguna, laporan'),
('operator','Mengelola data dosen dan master data, melihat laporan'),
('pimpinan','Hanya melihat dashboard, data dosen, dan laporan');

-- Akun demo: admin/admin123, operator/operator123, pimpinan/pimpinan123
INSERT INTO users (username,password,nama,role_id) VALUES
('admin','$2y$10$89dQQ8Pm7W1ULGBi9MeeBO46rP48Jp5o20hqXgNMU5IiZcKZyraKy','Administrator',1),
('operator','$2y$10$ICPpycuHi/yx.1RQJUliGu9wMgHQ0kfNON7ulhcuiR277rWfFE15W','Operator Fakultas Teknik',2),
('pimpinan','$2y$10$Wo7PqylDBkQC9QceszLC5O.CxmNc.acwz1hd3drbvMU1omphlEAVy','Dekan Fakultas Teknik',3);

INSERT INTO jabatan_fungsional (nama) VALUES ('Tenaga Pengajar'),('Asisten Ahli'),('Lektor'),('Lektor Kepala'),('Guru Besar');
INSERT INTO kepangkatan (nama) VALUES ('III/a'),('III/b'),('III/c'),('III/d'),('IV/a'),('IV/b');
INSERT INTO pendidikan (nama) VALUES ('S1'),('S2'),('S3');
INSERT INTO rumpun_ilmu (nama) VALUES ('Ilmu Komputer'),('Teknik Informatika'),('Sistem Informasi'),('Teknologi Informasi');
INSERT INTO status_pegawai (nama) VALUES ('Dosen Tetap Yayasan'),('Dosen Tidak Tetap'),('Dosen PNS DPK');
INSERT INTO ikatan_kerja (nama) VALUES ('Dosen Tetap'),('Dosen Tidak Tetap');
INSERT INTO status_aktivitas (nama) VALUES ('Aktif'),('Cuti'),('Tugas Belajar'),('Pensiun'),('Keluar');
INSERT INTO penempatan (nama) VALUES ('Program Studi Teknik Informatika'),('Program Studi Teknik Sipil'),('Program Studi Teknik Elektro');

INSERT INTO dosen (nomor_registrasi,nuptk,nama,nip,jabatan_id,pangkat_id,pendidikan_id,rumpun_id,status_serdos,status_pegawai_id,ikatan_kerja_id,status_aktivitas_id,penempatan_id) VALUES
('REG-0001','1234567890123456','Contoh Dosen Satu, M.Kom','-',3,3,2,1,'Sudah',1,1,1,1),
('REG-0002','2234567890123456','Contoh Dosen Dua, M.T','-',2,2,2,2,'Belum',1,1,1,1),
('REG-0003','3234567890123456','Contoh Dosen Tiga, Ph.D','-',4,5,3,1,'Sudah',3,1,2,1),
('REG-0004','4234567890123456','Contoh Dosen Empat, M.Kom','-',1,1,2,3,'Belum',2,2,1,1);
