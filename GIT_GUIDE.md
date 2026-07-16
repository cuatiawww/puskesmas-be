# Panduan Workflow Git (GitLab Mediacipta & GitHub)

Dokumen ini berisi tata cara dan alur kerja (workflow) Git yang digunakan pada project ini, khususnya untuk menangani sinkronisasi antara **GitLab Mediacipta** dan **GitHub**.

---

## 1. Memahami Struktur Remote Saat Ini

Project ini dikonfigurasi dengan dua remote utama:
*   **GitLab Mediacipta** (`https://git.mediaciptainformasi.co.id/...`): Digunakan sebagai default **Fetch** (tempat mengambil kode utama).
*   **GitHub** (`https://github.com/...`): Digunakan bersama teman/tim untuk kolaborasi branch tertentu.

### Konfigurasi Remote:
*   `origin` (Fetch): Mengambil data dari GitLab Mediacipta.
*   `origin` (Push): Mengirim data ke GitLab Mediacipta & GitHub sekaligus.
*   `github`: Remote khusus untuk berinteraksi langsung dengan GitHub.

---

## 2. Cara Mengambil (Pull/Fetch) Branch Teman dari GitHub

Jika teman Anda membuat branch baru bernama `nama-branch-teman` di GitHub dan Anda ingin mengambil/mengerjakannya secara lokal:

1.  **Ambil data terbaru dari GitHub:**
    ```bash
    git fetch github
    ```
2.  **Pindah ke branch tersebut dan buat branch lokal yang melacaknya:**
    ```bash
    git checkout -b nama-branch-teman github/nama-branch-teman
    ```
3.  **Untuk melakukan update (pull) di kemudian hari saat Anda berada di branch tersebut:**
    ```bash
    git pull github nama-branch-teman
    ```

---

## 3. Cara Menggabungkan (Merge) Branch ke `main`

Setelah Anda selesai mengerjakan branch tertentu atau ingin menggabungkan branch teman ke branch utama (`main`):

1.  **Pindah ke branch `main` lokal:**
    ```bash
    git checkout main
    ```
2.  **Pastikan repository lokal Anda bersih (tidak ada uncommitted changes):**
    ```bash
    git status
    ```
3.  **Gabungkan branch tersebut:**
    *   Jika menggabungkan branch lokal:
        ```bash
        git merge nama-branch-teman
        ```
    *   Jika ingin langsung menggabungkan dari remote GitHub tanpa pindah branch dulu:
        ```bash
        git merge github/nama-branch-teman
        ```

---

## 4. Cara Mengirim (Push) Update ke GitLab & GitHub Sekaligus

Karena remote `origin` Anda sudah dikonfigurasi untuk mem-push ke dua URL sekaligus, Anda cukup menjalankan perintah berikut ketika berada di branch `main`:

```bash
git push origin main
```

Git akan otomatis mengunggah perubahan ke:
1.  Repository GitLab Mediacipta.
2.  Repository GitHub.

---

## 5. Cheat Sheet Perintah Penting

| Perintah | Fungsi |
| :--- | :--- |
| `git status` | Melihat status file (apakah ada perubahan yang belum di-commit). |
| `git branch -a` | Melihat daftar semua branch (lokal maupun remote). |
| `git remote -v` | Melihat daftar remote repository yang terhubung beserta URL-nya. |
| `git log --oneline -n 5` | Melihat 5 commit terakhir secara ringkas. |
| `git checkout main` | Berpindah ke branch `main`. |
