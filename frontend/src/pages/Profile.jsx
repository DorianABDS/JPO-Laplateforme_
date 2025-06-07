import { useState } from "react";
import { Input } from "../components/Input";
import { Button } from "../components/Button";

export function Profile() {
  const [formData, setFormData] = useState({
    firstname: "",
    lastname: "",
    email: "",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log("Form submitted:", formData);
  };

  return (
    <section className="flex justify-center items-center mt-10 md:mt-32  ">
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
