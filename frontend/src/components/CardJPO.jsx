import React from "react";

export default function CardJPO({ jpo }) {
  return (
    <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
      <div className="flex flex-col md:flex-row">
        {/* Section image */}
        <div className="md:w-1/2">
          <img
            src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
            alt="Campus universitaire moderne avec couloirs spacieux"
            className="w-full h-64 md:h-full object-cover"
          />
        </div>

        {/* Section contenu */}
        <div className="md:w-1/2 bg-blue-600 text-white p-10 rounded-xl shadow-lg flex flex-col justify-center">
          <div className="space-y-6">
            <h1 className="text-3xl md:text-4xl font-extrabold leading-tight">
              {jpo.name}
            </h1>

            <div className="space-y-2 text-white/90">
              <p className="flex items-center gap-2">
                <span>Date :</span><strong>{jpo.date}</strong>
              </p>
              <p className="flex items-center gap-2">
                <span>Capacité :</span>{" "}
                <strong>{jpo.max_capacity} personnes</strong>
              </p>
              <p className="flex items-center gap-2">
                <span>Lieu :</span><strong>{jpo.campus_id}</strong>
              </p>
            </div>

            <p className="text-lg md:text-xl font-medium">
              Venez visiter le campus de <strong>Martigues</strong> et découvrez
              nos formations !
            </p>

            <button className="bg-white text-blue-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition duration-200 shadow-md">
              S'inscrire
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
