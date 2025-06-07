import { useEffect, useState } from "react";
import { CardJPO } from "../components/CardJPO";
import HeroImg from "../assets/img/bg-hero5.jpg";
import { HeroButton } from "../components/HeroButton";
import { CallToAction } from "../components/CallToAction";

// Page d'accueil affichant une bannière, des JPO à venir et un appel à l'action
export function Home() {
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
      {/* Section hero*/}
      <section
        className="bg-cover bg-center px-4 sm:px-8 md:px-20 flex flex-col justify-center text-white z-30"
        style={{
          backgroundImage: `url(${HeroImg})`,
          minHeight: "calc(100vh - 4rem)",
        }}
      >
        <h1 className="text-4xl sm:text-7xl md:text-8xl lg:text-9xl font-trench mb-2">
          LaPlateforme
        </h1>
        <p className="max-w-2xl text-base sm:text-lg md:text-xl font-Poppins mb-6">
          Découvrez nos campus grâce à nos journée porte ouverte, rencontrez nos
          enseignants et explorez nos formations.
        </p>
        <HeroButton label="Découvrir" to="/events" className="" />
      </section>

      {/* JPO*/}
      <section>
        <p className="font-trench text-4xl font-bold text-[#0062FF] mt-20 mb-20 flex justify-center text-center px-4">
          Journée porte ouverte à venir
        </p>
        <CardJPO
          jpo={{
            name: "Martigues - Journée porte ouverte",
            date: "2025-06-16",
            max_capacity: 100,
            campus_id: "Martigues",
          }}
          occupationPercentage={50}
        />
      </section>

      <section>
        <CardJPO
          jpo={{
            name: "Martigues - Journée porte ouverte",
            date: "2025-06-17",
            max_capacity: 50,
            campus_id: "Martigues",
          }}
          occupationPercentage={81}
        />
      </section>

      <section>
        <CardJPO
          jpo={{
            name: "Martigues - Journée porte ouverte",
            date: "2025-06-17",
            max_capacity: 50,
            campus_id: "Martigues",
          }}
          occupationPercentage={100}
        />
      </section>

      <CallToAction />

      {/* Section d'affichage dynamique de toutes les JPO */}
      {/*
      <section className="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        {jpos.map((open_day) => (
          <CardJPO key={open_day.id} jpo={open_day} />
        ))}
      </section>
      */}
    </div>
  );
}
