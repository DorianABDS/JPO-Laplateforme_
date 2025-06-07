import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { CardJPO } from "../components/CardJPO";

// Affiche le détail d'une JPO spécifique via son ID dans l'URL
export function JpoDetail() {
  const { id } = useParams();
  const [jpo, setJpo] = useState(null);

  // Requête API pour récupérer les données de la JPO au montage
  useEffect(() => {
    fetch(`${import.meta.env.VITE_API_URL}/api/jpo.php?id=${id}`)
      .then((res) => res.json())
      .then((data) => setJpo(data))
      .catch((err) => console.error("Erreur API :", err));
  }, [id]);

  if (!jpo) return <p className="p-4">Chargement...</p>;

  return (
    <Link to={`/jpo/${jpo.id}`} className="block hover:shadow-lg transition">
      <CardJPO />
    </Link>
  );
}
