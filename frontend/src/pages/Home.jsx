import { useEffect, useState } from "react";
import CardJPO from "../components/CardJPO";

export default function Home() {
  const [jpos, setJpos] = useState([]);

  useEffect(() => {
    fetch(`${import.meta.env.VITE_API_URL}/api/jpo.php`)
      .then((res) => res.json())
      .then((data) => {
        setJpos(data);
      })
      .catch((err) => {
        console.error("Erreur lors de l'appel API :", err);
      });
  }, []);

  return (
    <div>
      {/* Hero Section */}
      <section
        className="relative bg-cover bg-center h-screen p-0 flex flex-col justify-center items-center text-white"
        style={{ backgroundImage: "url('../src/assets/img/bg-hero.svg')" }}
      >
        <h1 className="text-4xl font-bold mb-2">
          Bienvenue aux Journées Portes Ouvertes
        </h1>
        <p className="max-w-xl text-center">
          Découvrez nos campus, rencontrez les enseignants et explorez nos
          formations.
        </p>
      </section>

      {/* Liste des JPO */}
      <section className="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        {jpos.map((open_day) => (
          <CardJPO key={open_day.id} jpo={open_day} />
        ))}
      </section>
    </div>
  );
}
