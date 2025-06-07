// Hooks React
import { useState } from "react";

// Composants personnalisés
import { Input } from "../components/Input";
import { Button } from "../components/Button";

export function Profile() {
  // État local pour stocker les données du formulaire
  const [formData, setFormData] = useState({
    firstname: "",
    lastname: "",
    email: "",
  });

  // Gère les changements dans les champs du formulaire
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  // Gère la soumission du formulaire
  const handleSubmit = (e) => {
    e.preventDefault();
    console.log("Form submitted:", formData);
  };

  return (
    <section className="flex justify-center items-center mt-10 md:mt-32">
      {/* Formulaire d'édition du profil utilisateur */}
      <form
        onSubmit={handleSubmit}
        className="flex flex-col flex-grow justify-center items-center p-10 rounded-lg bg-[#0062FF] gap-6 max-w-md w-full mx-4 shadow-lg"
      >
        <Input
          name="firstname"
          value={formData.firstname}
          onChange={handleChange}
          placeholder="Prénom"
          type="text"
        />
        <Input
          name="lastname"
          value={formData.lastname}
          onChange={handleChange}
          placeholder="Nom"
          type="text"
        />
        <Input
          name="email"
          value={formData.email}
          onChange={handleChange}
          placeholder="Email"
          type="email"
        />
        <Button
          type="submit"
          label="Enregistrer"
          className="w-full"
        />
      </form>
    </section>
  );
}
