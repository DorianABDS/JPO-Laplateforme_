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
        {/* Éléments graphiques décoratifs */}
        <div className="absolute top-0 left-0 w-full h-full pointer-events-none">
          {/* Cercle décoratif en haut à droite */}
          <div className="absolute -top-32 -right-32 w-64 h-64 rounded-full bg-gradient-to-br from-[#0062FF]/10 to-[#0062FF]/5 blur-3xl"></div>
          
          {/* Forme géométrique en bas à gauche */}
          <div className="absolute -bottom-20 -left-20 w-40 h-40 rotate-45 bg-gradient-to-tr from-[#0062FF]/8 to-transparent rounded-3xl"></div>
          
          {/* Lignes décoratives */}
          <div className="absolute top-1/4 right-1/4 w-32 h-0.5 bg-gradient-to-r from-[#0062FF]/20 to-transparent rotate-12"></div>
          <div className="absolute bottom-1/3 left-1/3 w-24 h-0.5 bg-gradient-to-l from-[#0062FF]/15 to-transparent -rotate-12"></div>
          
          {/* Points décoratifs */}
          <div className="absolute top-1/2 left-1/4 w-2 h-2 rounded-full bg-[#0062FF]/30"></div>
          <div className="absolute top-1/3 right-1/3 w-1.5 h-1.5 rounded-full bg-[#0062FF]/20"></div>
          <div className="absolute bottom-1/4 right-1/4 w-3 h-3 rounded-full bg-[#0062FF]/15"></div>
        </div>

        {/* Contenu principal */}
        <div className="relative z-10">
          {/* Titre avec effet moderne */}
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

          {/* Container pour les cartes */}
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

        {/* Effet de vague en bas de section */}
        <div className="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-gray-50/50 to-transparent"></div>
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