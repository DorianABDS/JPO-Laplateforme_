import { useEffect, useState } from "react";
import { EventCards } from "../components/EventCards";
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
        className="bg-cover bg-center px-4 sm:px-8 md:px-20 flex flex-col justify-center text-white z-30 relative overflow-hidden"
        style={{
          backgroundImage: `url(${HeroImg})`,
          minHeight: "calc(100vh - 4rem)",
        }}
      >
        {/* Overlay subtil pour améliorer la lisibilité */}
        <div className="absolute inset-0 bg-black/20"></div>
        
        <div className="relative z-10">
          <h1 className="text-4xl font-trench mb-2 text-center sm:text-7xl md:text-8xl md:text-start lg:text-9xl drop-shadow-lg">
            LaPlateforme
          </h1>
          <p className="max-w-2xl text-base text-center sm:text-lg md:text-xl md:text-start font-Poppins mb-6 drop-shadow-md">
            Découvrez nos campus grâce à nos journée porte ouverte, rencontrez nos
            enseignants et explorez nos formations.
          </p>
          <HeroButton label="Découvrir" to="/events" className="" />
        </div>
      </section>

      {/* Section JPO avec design moderne */}
      <section className="bg-white relative overflow-hidden py-20">
        {/* Contenu principal */}
        <div className="relative z-10">
          {/* Titre */}
          <div className="text-center mb-16">
            <div className="inline-block relative">
              <h2 className="font-trench text-4xl sm:text-5xl lg:text-6xl font-bold text-[#0062FF] mb-4 px-4">
                Journée porte ouverte à venir
              </h2>
              {/* Ligne décorative sous le titre */}
              <div className="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-transparent via-[#0062FF] to-transparent rounded-full"></div>
            </div>
            <p className="text-gray-600 text-lg mt-6 max-w-2xl mx-auto px-4">
              Découvrez nos formations et rencontrez nos équipes lors de nos prochaines journées portes ouvertes
            </p>
          </div>

          {/* Container cartes */}
          <div className="">
            <EventCards
              jpo={{
                name: "Martigues - Journée porte ouverte",
                date: "2025-06-16",
                max_capacity: 100,
                campus_id: "Martigues",
              }}
              occupationPercentage={50}
            />

            <EventCards
              jpo={{
                name: "Martigues - Journée porte ouverte",
                date: "2025-06-17",
                max_capacity: 50,
                campus_id: "Martigues",
              }}
              occupationPercentage={81}
            />

            <EventCards
              jpo={{
                name: "Martigues - Journée porte ouverte",
                date: "2025-06-17",
                max_capacity: 50,
                campus_id: "Martigues",
              }}
              occupationPercentage={100}
            />
          </div>
        </div>

      </section>

      <CallToAction />

      {/* Section d'affichage dynamique de toutes les JPO */}
      {/*
      <section className="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        {jpos.map((open_day) => (
          <EventCards key={open_day.id} jpo={open_day} />
        ))}
      </section>
      */}
    </div>
  );
}