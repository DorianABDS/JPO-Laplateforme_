import { useEffect, useState } from "react";
import CardJPO from "../components/CardJPO";
import HeroImg from "../assets/img/bg-hero5.jpg";
import Button from "../components/Button";

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
      {/* Hero */}
      <section
        className="bg-cover bg-center px-20 flex flex-col justify-center text-white"
        style={{
          backgroundImage: `url(${HeroImg})`,
          minHeight: "calc(100vh - 4rem)",
        }}
      >
        <h1 className="text-9xl font-trench mb-2">LaPlateforme</h1>
        <p className="max-w-2xl text-xl font-Poppins ">
          Découvrez nos campus grâce à nos journée porte ouverte, rencontrez nos enseignants et explorez nos
          formations.
        </p>
        <Button />
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
