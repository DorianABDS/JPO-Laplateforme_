import { useEffect, useState } from "react";
import CardJPO from "../components/CardJPO";

export default function Home() {
    const {jpos, setJpos} =  useState([])

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
    <div className="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      {jpos.map((open_day) => (
        <CardJPO key={open_day.id} jpo={open_day} />
      ))}
    </div>
  );
}
