import React, { useEffect, useState } from "react";

// Composant compteur à rebours jusqu'à une date cible
export function CountdownTimer({ targetDate }) {
  // Calcule le temps restant (jours, heures, minutes)
  const calculateTimeLeft = () => {
    const difference = +new Date(targetDate) - +new Date();
    if (difference <= 0) return null; // date passée

    return {
      days: Math.floor(difference / (1000 * 60 * 60 * 24)),
      hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
      minutes: Math.floor((difference / 1000 / 60) % 60),
    };
  };

  const [timeLeft, setTimeLeft] = useState(calculateTimeLeft());

  // Met à jour le compteur toutes les secondes
  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft(calculateTimeLeft());
    }, 1000);

    return () => clearInterval(timer); // nettoyage à la suppression du composant
  }, [targetDate]);

  // Affiche message si date dépassée
  if (!timeLeft) {
    return (
      <div className="text-red-500 text-xl font-semibold">
        Inscriptions terminées
      </div>
    );
  }

  // Affiche le temps restant formaté
  return (
    <div className="text-white text-xl font-semibold flex flex-row gap-2 items-center">
      <div>
        <span>{timeLeft.days}</span> j{timeLeft.days > 1 ? "ours" : ""}
      </div>
      <div>
        <span>{String(timeLeft.hours).padStart(2, "0")}</span> h
      </div>
      <div>
        <span>{String(timeLeft.minutes).padStart(2, "0")}</span> min
      </div>
    </div>
  );
}
