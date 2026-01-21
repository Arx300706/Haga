# Haga Hosting - Documentation du Système Client

## 📋 Vue d'ensemble

Le système Haga Hosting inclut une gestion complète des clients avec inscription, upload de fichiers, et attribution de domaines.

---

## 🔐 Authentification

### Admin
- **URL** : `https://192.168.88.163/web/login.php`
- **Identifiants** : 
  - Utilisateur: `admin`
  - Mot de passe: `hagasite`
- **Accès** : Création et gestion des projets, gestion des clients

### Clients
- **URL d'inscription** : `https://192.168.88.163/web/register.php`
- **URL de connexion** : `https://192.168.88.163/web/client_index.php`
- **Données stockées** : `web/donnee.txt`

---

## 📁 Structure des Fichiers Client

### donnee.txt
Format: `Nom|Email|Téléphone|Entreprise|MotDePasse|DateCreation|Domaine|Servername`

**Exemple:**
```
Jean Dupont|jean@example.com|+33 1 23 45 67 89|ACME Corp|monmdp123|2026-01-21 15:30:00|example.com|ns1.example.com
Marie Martin|marie@example.com|+33 1 98 76 54 32|Tech Solutions|securepass|2026-01-21 16:00:00||
```

### client_uploads/
- Dossier : `client_uploads/[HASH_EMAIL]/`
- Contient les fichiers uploadés par chaque client

---

## 🎯 Workflow Client

### 1️⃣ Inscription
```
1. Client va sur register.php
2. Remplit le formulaire (Nom, Email, Téléphone, Entreprise, Mot de passe)
3. Compte créé dans donnee.txt
4. Admin reçoit une notification (si mail configuré)
```

### 2️⃣ Connexion
```
1. Client va sur client_index.php
2. Saisit Email + Mot de passe
3. Redirigé vers client_dashboard.php
```

### 3️⃣ Upload de Fichiers
```
1. Client upload son fichier via le dashboard
2. Fichier stocké dans client_uploads/[HASH_EMAIL]/
3. Admin reçoit une notification
4. Admin peut voir le fichier et l'approuver
```

### 4️⃣ Attribution de Domaine
```
1. Admin va sur manage_clients.php
2. Clique sur "Éditer" pour le client
3. Remplit le Domaine et Servername
4. Client reçoit son domaine et peut accéder à son site
```

---

## 🛠️ Pages Principales

### Pour les Clients

| Page | URL | Fonction |
|------|-----|----------|
| Inscription | `/web/register.php` | Créer un compte |
| Connexion | `/web/client_index.php` | Se connecter |
| Dashboard | `/web/client_dashboard.php` | Voir ses infos et uploader des fichiers |
| Déconnexion | `/web/logout.php?type=client` | Se déconnecter |

### Pour l'Admin

| Page | URL | Fonction |
|------|-----|----------|
| Connexion | `/web/login.php` | Se connecter |
| Dashboard | `/web/index.php` | Voir les demandes en attente |
| Créer Projet | `/web/create_project.php` | Créer un nouveau projet web |
| Gérer Clients | `/web/manage_clients.php` | Attribuer domaines et servername |
| Déconnexion | `/web/logout.php` | Se déconnecter |

---

## 📧 Système de Notifications (Email)

Quand un admin configure un email, les notifications suivantes sont envoyées:

1. **Nouvelle inscription** - Admin notifié
2. **Nouveau fichier uploadé** - Admin notifié
3. **Domaine attribué** - Client notifié

**Configuration** : Éditer `email_notification.php` et modifier `ADMIN_EMAIL`

---

## 🔧 Fichiers de Configuration

### config.php
```php
define('SERVER_BASE_URL', 'http://127.0.0.1:8080');
define('SERVER_IP', '192.168.88.163');
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'hagasite');
```

### email_notification.php
Contient les fonctions pour envoyer des emails:
- `sendEmailNotification()` - Envoi générique
- `notifyNewClientRegistration()` - Nouvelle inscription
- `notifyFileUpload()` - Upload de fichier
- `notifyClientDomainAssignment()` - Attribution de domaine

---

## 📊 Gestion des Données

### Ajouter Manuellement un Client

Créer une ligne dans `donnee.txt`:
```
NomClient|email@example.com|+33123456789|Entreprise|motdepasse|2026-01-21 12:00:00|domaine.com|ns1.domaine.com
```

### Supprimer un Client

1. Ouvrir `donnee.txt`
2. Supprimer la ligne du client
3. Supprimer le dossier `client_uploads/[HASH_EMAIL]/`

### Réinitialiser un Mot de Passe

1. Ouvrir `donnee.txt`
2. Modifier le mot de passe (colonne 5)
3. Enregistrer

---

## ⚙️ Intégration avec le Serveur Java

Les domaines créés par l'admin sont envoyés au serveur Java (port 8080) pour:
- Créer des zones DNS
- Configurer les résolutions de domaine

**URL** : `http://127.0.0.1:8080/createZone?domain=example.com&zone=project&ip=192.168.88.163`

---

## 🚀 Tests

### Test 1: Créer un compte client
```
1. Aller sur /web/register.php
2. Remplir le formulaire
3. Vérifier dans donnee.txt que la ligne est créée
4. Se connecter avec les identifiants
```

### Test 2: Upload de fichier
```
1. Connecté en tant que client
2. Uploader un fichier ZIP
3. Vérifier qu'il aparaît dans "Vos Fichiers"
4. Vérifier le dossier client_uploads/
```

### Test 3: Attribuer un domaine
```
1. Admin va sur manage_clients.php
2. Clique sur "Éditer" pour un client
3. Remplit Domaine et Servername
4. Clique sur "Mettre à jour"
5. Vérifier que donnee.txt a été modifié
```

---

## 🔒 Sécurité

### Bonnes Pratiques Implémentées
- ✅ Validation des emails
- ✅ Vérification des sessions
- ✅ Préventions des injections SQL (utilisation de trim/htmlspecialchars)
- ✅ Vérification des extensions de fichier
- ✅ Limitation de la taille des fichiers (100 MB)

### À Faire
- [ ] Hasher les mots de passe (password_hash/password_verify)
- [ ] Implémenter CSRF tokens
- [ ] Ajouter rate limiting sur les uploads
- [ ] Logs d'accès Admin
- [ ] Chiffrement des données sensibles

---

## 🐛 Dépannage

### "Erreur lors de la création du compte"
- Vérifier que `donnee.txt` a les bonnes permissions (666)
- Vérifier l'espace disque disponible

### "Client ne peut pas se connecter"
- Vérifier que email + mot de passe sont corrects dans `donnee.txt`
- Vérifier que la session PHP est activée

### "Fichier non uploadé"
- Vérifier que le dossier `client_uploads/` existe et a les permissions 755
- Vérifier l'extension du fichier (doit être dans la liste autorisée)
- Vérifier la taille (max 100 MB)

### "Admin ne reçoit pas de notifications email"
- Vérifier que `ADMIN_EMAIL` est configuré
- Vérifier que le serveur mail est disponible
- Consulter les logs : `tail /var/log/mail.log`

---

## 📞 Support

Pour toute question ou problème:
1. Consultez cette documentation
2. Vérifiez les logs: `/var/log/apache2/error.log`
3. Contactez l'administrateur du serveur

---

**Dernière mise à jour** : 21 janvier 2026
**Version** : 1.0.0
