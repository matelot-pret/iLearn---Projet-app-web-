<?php

$admin_ressources_path      = "/myMoodle/public/admin_ressources.php";
class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(
            "mysql:host=localhost;dbname=projetphp;charset=utf8",
            "BDProjetPatrick&Jean",
            "projetphp_pass",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }

    public function get_user_by_matricule(string $matricule): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM utilisateurs WHERE matricule = :m"
        );
        $stmt->execute([':m' => $matricule]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create_user(
        string $nom,
        string $prenom,
        string $matricule,
        string $passwordHash,
        int $estAdmin,
        ?string $avatar
    ): void {
        $stmt = $this->pdo->prepare("INSERT INTO utilisateurs
            (nom, prenom, matricule, mot_de_passe, est_admin, avatar)
            VALUES (:n, :p, :m, :mdp, :a, :av)
        ");
        $stmt->execute([
            ':n' => $nom,
            ':p' => $prenom,
            ':m' => $matricule,
            ':mdp' => $passwordHash,
            ':a' => $estAdmin,
            ':av' => $avatar
        ]);
    }

    public function get_ressources_by_lesson(int $coursId, int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT r.*,
                    rl.utilisateur_id IS NOT NULL AS est_lue
            FROM ressources r
            LEFT JOIN ressources_lues rl
                ON rl.ressource_id = r.id
                AND rl.utilisateur_id = :uid
            WHERE r.cours_id = :cid
                AND r.statut = 'approuve'
            ORDER BY r.date_ajout DESC
        ");
        $stmt->execute([
            ':cid' => $coursId,
            ':uid' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_ressource(
        string $titre,
        string $type,
        string $contenu,
        int $coursId,
        int $userId
    ): void {
        $stmt = $this->pdo->prepare("INSERT INTO ressources
            (titre, type, contenu, cours_id, utilisateur_id, statut, date_ajout)
            VALUES (:t, :ty, :c, :cid, :uid, 'en_attente', NOW())
        ");
        $stmt->execute([
            ':t' => $titre,
            ':ty' => $type,
            ':c' => $contenu,
            ':cid' => $coursId,
            ':uid' => $userId
        ]);
    }

    public function mark_ressource_as_read(int $ressourceId, int $userId): void
    {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO ressources_lues (ressource_id, utilisateur_id)
            VALUES (:r, :u)
        ");
        $stmt->execute([
            ':r' => $ressourceId,
            ':u' => $userId
        ]);
    }

    public function get_user_ressource_history(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT r.titre, r.type, r.statut, r.date_ajout, c.nom AS cours
            FROM ressources r
            JOIN cours c ON c.id = r.cours_id
            WHERE r.utilisateur_id = :u
            ORDER BY r.date_ajout DESC
        ");
        $stmt->execute([':u' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_cours_with_ressources(): array
    {
        $stmt = $this->pdo->query("SELECT c.id, c.nom, c.bloc, c.section,
                COUNT(r.id) AS nb_ressources,
                MAX(r.date_ajout) AS derniere_ressource
            FROM cours c
            LEFT JOIN ressources r
                ON r.cours_id = c.id
                AND r.statut = 'approuve'
            GROUP BY c.id, c.nom, c.bloc, c.section
            HAVING COUNT(r.id) > 0
            ORDER BY derniere_ressource DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function get_cours_by_id(int $cours_id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nom
            FROM cours
            WHERE id = :id
        ");
        $stmt->execute([':id' => $cours_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    public function get_ressources_by_cours(int $cours_id, int $user_id): array
    {
        $stmt = $this->pdo->prepare("SELECT r.id,
                    r.titre,
                    r.type,
                    r.contenu,
                    r.date_ajout,
                    rl.utilisateur_id IS NOT NULL AS est_lue
            FROM ressources r
            LEFT JOIN ressources_lues rl
                ON rl.ressource_id = r.id
                AND rl.utilisateur_id = :uid
            WHERE r.cours_id = :cid
                AND r.statut = 'approuve'
            ORDER BY r.date_ajout DESC
        ");

        $stmt->execute([
            ':cid' => $cours_id,
            ':uid' => $user_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function get_all_cours(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, nom, bloc, section
            FROM cours
            ORDER BY nom
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create_cours(string $nom, string $bloc, string $section): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO cours (nom, bloc, section)
            VALUES (:n, :b, :s)
        ");
        $stmt->execute([
            ':n' => $nom,
            ':b' => $bloc,
            ':s' => $section
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function get_pending_ressources(): array
    {
    $stmt = $this->pdo->prepare("SELECT r.id,
                r.titre,
                r.type,
                r.contenu,
                r.date_ajout,
                u.nom,
                u.prenom,
                c.nom AS cours_nom
        FROM ressources r
        JOIN utilisateurs u ON r.utilisateur_id = u.id
        JOIN cours c ON r.cours_id = c.id
        WHERE r.statut = 'en_attente'
        ORDER BY r.date_ajout ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_ressource_status(int $ressource_id, string $statut): void
    {
        $stmt = $this->pdo->prepare("UPDATE ressources
            SET statut = :statut
            WHERE id = :id
        ");
        $stmt->execute([
            ':statut' => $statut,
            ':id' => $ressource_id
        ]);
    }

    public function get_ressources_by_user(int $user_id): array
    {
        $stmt = $this->pdo->prepare("SELECT r.titre,
                r.type,
                r.statut,
                r.date_ajout,
                c.nom AS cours_nom
            FROM ressources r
            JOIN cours c ON r.cours_id = c.id
            WHERE r.utilisateur_id = :uid
            ORDER BY r.date_ajout DESC
        ");

        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public function get_ressource_by_id(int $ressource_id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                r.id,
                r.titre,
                r.type,
                r.contenu,
                r.statut,
                r.date_ajout,
                r.cours_id,
                c.nom AS cours_nom,
                u.id AS utilisateur_id,
                u.nom AS utilisateur_nom,
                u.prenom AS utilisateur_prenom
            FROM ressources r
            JOIN cours c ON c.id = r.cours_id
            JOIN utilisateurs u ON u.id = r.utilisateur_id
            WHERE r.id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $ressource_id
        ]);

        $ressource = $stmt->fetch(PDO::FETCH_ASSOC);

        return $ressource ?: null;
    }


}

