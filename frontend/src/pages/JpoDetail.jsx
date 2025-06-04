import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";

export default function JpoDetail() {
  const { id } = useParams();
  const [jpo, setJpo] = useState(null);

  useEffect(() => {
    fetch(`${import.meta.env.VITE_API_URL}/api/jpo.php?id=${id}`)
      .then((res) => res.json())
      .then((data) => setJpo(data))
      .catch((err) => console.error("Erreur API :", err));
  }, [id]);

  if (!jpo) return <p className="p-4">Chargement...</p>;

  return (
    <Link to={`/jpo/${jpo.id}`} className="block hover:shadow-lg transition">
      <div className="p-6 max-w-3xl mx-auto bg-white rounded-xl shadow-md space-y-6">
        <h1 className="text-3xl font-bold text-blue-600">{jpo.name}</h1>
        <p><strong>Date :</strong> {jpo.date}</p>
        <p><strong>Capacité :</strong> {jpo.max_capacity}</p>
        <p><strong>Campus :</strong> {jpo.campus_id}</p>
        <p className="text-gray-700">{jpo.description}</p>
        <button className="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          S'inscrire
        </button>
      </div>
    </Link>
  );
}
