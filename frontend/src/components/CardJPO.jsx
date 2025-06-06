import React from "react";
import CountdownTimer from "./TimerCountdown";
import Button from "./Button";
import { Calendar, Users, MapPin, Clock } from "lucide-react";

export default function CardJPO({ jpo }) {
  const createDateTime = (dateStr, timeStr) => {
    const date = new Date(dateStr);
    if (!timeStr) return date;
    const [hours, minutes] = timeStr.split(":").map(Number);
    date.setHours(hours ?? 0, minutes ?? 0, 0, 0);
    return date;
  };

  const openingHour = "14:00";
  const targetDateWithTime = createDateTime(jpo.date, openingHour);

  return (
    <div className="w-[60%] mx-auto mb-10 bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
      <div className="flex flex-col md:flex-row">
        {/* Image */}
        <div className="md:w-1/2 h-48 md:h-auto">
          <img
            src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
            alt="Campus universitaire"
            className="w-full h-full object-cover"
          />
        </div>

        {/* Contenu */}
        <div className="md:w-1/2 bg-[#0062FF] text-white p-6 flex flex-col justify-between rounded-br-xl">
          <div className="space-y-2">
            <h2 className="text-xl md:text-2xl font-bold">{jpo.name}</h2>

            {/* Countdown avec largeur flexible */}
            <div className="w-full min-w-0">
              <CountdownTimer targetDate={targetDateWithTime} />
            </div>

            <div className="space-y-1 text-white/90 text-xs md:text-sm">
              <p className="flex items-center gap-2">
                <Calendar className="w-4 h-4" />
                <span>
                  {new Date(jpo.date).toLocaleDateString("fr-FR", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                  })}
                </span>
              </p>

              <p className="flex items-center gap-2">
                <Clock className="w-4 h-4" />
                <span>
                  Ouverture : <strong>9h00</strong>
                </span>
              </p>

              <p className="flex items-center gap-2">
                <Users className="w-4 h-4" />
                <span>
                  Capacité : <strong>{jpo.max_capacity}</strong>
                </span>
              </p>

              <p className="flex items-center gap-2">
                <MapPin className="w-4 h-4" />
                <span>
                  Lieu : <strong>{jpo.campus_id}</strong>
                </span>
              </p>
            </div>

            <p className="text-xs md:text-sm font-medium mt-1">
              Venez découvrir nos formations à <strong>Martigues</strong> !
            </p>
          </div>

          <Button 
            to="/jpo:id" 
            label="Voir les détails" 
            className="" 
          />

        </div>
      </div>
    </div>
  );
}