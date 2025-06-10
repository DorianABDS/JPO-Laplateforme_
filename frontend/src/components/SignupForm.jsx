import { useEffect, useState } from "react";

export function SignupForm() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [submittedData, setSubmittedData] = useState(null);
  const [errors, setErrors] = useState({});
  const [successMessage, setSuccessMessage] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (successMessage) {
      const timer = setTimeout(() => setSuccessMessage(""), 5000);
      return () => clearTimeout(timer);
    }
  }, [successMessage]);

  function handleSubmit(e) {
    e.preventDefault();
    const newErrors = {};

    // --- Validation des champs ---
    if (!name) newErrors.name = "Le nom est requis.";
    if (!email) newErrors.email = "L'email est requis.";
    if (!password) newErrors.password = "Le mot de passe est requis.";
    if (password !== confirmPassword) {
      newErrors.confirmPassword = "Les mots de passe ne correspondent pas.";
    }

    // Regex email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
      newErrors.email = "L'email n'est pas valide.";
    }

    // Regex mot de passe sécurisé
    const passwordRegex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (password && !passwordRegex.test(password)) {
      newErrors.password =
        "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
    }

    // --- Gestion des erreurs ---
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    // --- Aucun problème : on traite l'envoi ---
    setErrors({});
    setIsLoading(true);

    // Simuler un envoi (remplace par appel API réel ensuite)
    setTimeout(() => {
      setSubmittedData({ name, email });
      setSuccessMessage("Inscription réussie !");
      setIsLoading(false);

      // Réinitialisation du formulaire
      setName("");
      setEmail("");
      setPassword("");
      setConfirmPassword("");
    }, 1000);
  }

  return (
    <>
      <form onSubmit={handleSubmit}>
        <input
          type="text"
          placeholder="Nom"
          value={name}
          onChange={(e) => setName(e.target.value)}
        />
        {errors.name && <p className="text-red-600">{errors.name}</p>}

        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        {errors.email && <p className="text-red-600">{errors.email}</p>}

        <input
          type="password"
          placeholder="Mot de passe"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />
        {errors.password && <p className="text-red-600">{errors.password}</p>}

        <input
          type="password"
          placeholder="Confirmer le mot de passe"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
        />
        {errors.confirmPassword && (
          <p className="text-red-600">{errors.confirmPassword}</p>
        )}

        <button type="submit" disabled={isLoading}>
          {isLoading ? "Envoi..." : "S'inscrire"}
        </button>
      </form>

      {successMessage && <p className="text-green-600">{successMessage}</p>}

      {submittedData && (
        <div>
          <h2>Inscription envoyée :</h2>
          <p>Nom : {submittedData.name}</p>
          <p>Email : {submittedData.email}</p>
        </div>
      )}
    </>
  );
}
