-- DROP in correct order (children first) to avoid FK errors
DROP TABLE IF EXISTS rtl;
DROP TABLE IF EXISTS evaluasi;
DROP TABLE IF EXISTS evaluasi_bukti;
DROP TABLE IF EXISTS renstra;
DROP TABLE IF EXISTS renstra_target;
DROP TABLE IF EXISTS renstra_indikator;
DROP TABLE IF EXISTS renstra_kegiatan;
DROP TABLE IF EXISTS renstra_kategori;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS jabatan;
DROP TABLE IF EXISTS prodi;

-- Parent: Prodi
CREATE TABLE prodi (
  id_prodi INT AUTO_INCREMENT PRIMARY KEY,
  nama_prodi VARCHAR(255) NOT NULL,
  fakultas VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parent: Jabatan
CREATE TABLE jabatan (
  id_jabatan INT AUTO_INCREMENT PRIMARY KEY,
  nama_jabatan VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users (parent for many tables). Note: prodi_id kept NOT NULL — change to NULL if you want nullable.
CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  prodi_id INT NOT NULL,
  id_jabatan INT NULL,
  nama_user VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  -- other user fields...
  CONSTRAINT fk_users_prodi FOREIGN KEY (prodi_id) REFERENCES prodi(id_prodi) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_users_jabatan FOREIGN KEY (id_jabatan) REFERENCES jabatan(id_jabatan) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renstra Kategori (child of users)
CREATE TABLE renstra_kategori (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  jenis_kategori VARCHAR(200) NOT NULL,
  CONSTRAINT fk_renstra_kategori_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renstra Kegiatan (child of users and renstra_kategori)
CREATE TABLE renstra_kegiatan (
  id_kegiatan INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  id_kategori INT NULL,
  nomor VARCHAR(50),
  nama_kegiatan VARCHAR(500) NOT NULL,
  CONSTRAINT fk_renstra_kegiatan_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_kegiatan_kategori FOREIGN KEY (id_kategori) REFERENCES renstra_kategori(id_kategori) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renstra Indikator (child of renstra_kegiatan and users)
CREATE TABLE renstra_indikator (
  id_indikator INT AUTO_INCREMENT PRIMARY KEY,
  id_kegiatan INT NOT NULL,
  user_id INT NOT NULL,
  indikator TEXT NOT NULL,
  keterangan TEXT,
  CONSTRAINT fk_renstra_indikator_kegiatan FOREIGN KEY (id_kegiatan) REFERENCES renstra_kegiatan(id_kegiatan) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_indikator_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renstra Target (child of renstra_indikator and users)
CREATE TABLE renstra_target (
  id_target INT AUTO_INCREMENT PRIMARY KEY,
  id_indikator INT NOT NULL,
  user_id INT NOT NULL,
  tahun YEAR NOT NULL,
  target_value VARCHAR(100) NOT NULL,
  CONSTRAINT fk_renstra_target_indikator FOREIGN KEY (id_indikator) REFERENCES renstra_indikator(id_indikator) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_target_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renstra (references kegiatan, target, kategori, indikator, user)
CREATE TABLE renstra (
  id_renstra INT AUTO_INCREMENT PRIMARY KEY,
  kode_renstra VARCHAR(100),
  indikator TEXT,
  user_id INT NOT NULL,
  id_kegiatan INT NULL,
  id_target INT NULL,
  id_kategori INT NULL,
  id_indikator INT NULL,
  keterangan TEXT,
  CONSTRAINT fk_renstra_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_kegiatan FOREIGN KEY (id_kegiatan) REFERENCES renstra_kegiatan(id_kegiatan) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_target FOREIGN KEY (id_target) REFERENCES renstra_target(id_target) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_kategori FOREIGN KEY (id_kategori) REFERENCES renstra_kategori(id_kategori) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_renstra_indikator FOREIGN KEY (id_indikator) REFERENCES renstra_indikator(id_indikator) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluasi Bukti
-- NOTE: user_id must be NULLABLE because the FK uses ON DELETE SET NULL below.
CREATE TABLE evaluasi_bukti (
  id_bukti INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,                        -- changed to NULL to allow SET NULL on delete
  upload_bukti VARCHAR(512),               -- filename or path
  link_bukti VARCHAR(1024),
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evaluasi_bukti_user FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluasi (references prodi, renstra, renstra_target, evaluasi_bukti, users)
-- All FK columns that use ON DELETE SET NULL must be nullable.
CREATE TABLE evaluasi (
  id_eval INT AUTO_INCREMENT PRIMARY KEY,
  realisasi TEXT,
  ketercapaian TEXT,
  akar_masalah TEXT,
  faktor_pendukung TEXT,
  status INT,
  id_prodi INT NULL,
  id_renstra INT NULL,
  id_target INT NULL,
  id_bukti INT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evaluasi_prodi FOREIGN KEY (id_prodi) REFERENCES prodi(id_prodi) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_evaluasi_renstra FOREIGN KEY (id_renstra) REFERENCES renstra(id_renstra) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_evaluasi_target FOREIGN KEY (id_target) REFERENCES renstra_target(id_target) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_evaluasi_bukti FOREIGN KEY (id_bukti) REFERENCES evaluasi_bukti(id_bukti) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_evaluasi_createdby FOREIGN KEY (created_by) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RTL (child of evaluasi, users, prodi)
CREATE TABLE rtl (
  id_rtl INT AUTO_INCREMENT PRIMARY KEY,
  rtl TEXT NOT NULL,
  deadline DATE,
  pic_rtl VARCHAR(255),
  bukti_rtl VARCHAR(512),
  status VARCHAR(100),
  keterangan TEXT,
  eval_id INT NULL,    -- FK to evaluasi
  users_id INT NULL,   -- responsible user
  prodi_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rtl_evaluasi FOREIGN KEY (eval_id) REFERENCES evaluasi(id_eval) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rtl_user FOREIGN KEY (users_id) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_rtl_prodi FOREIGN KEY (prodi_id) REFERENCES prodi(id_prodi) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
