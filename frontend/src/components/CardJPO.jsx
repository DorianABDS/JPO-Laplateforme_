import React from "react";
import { CountdownTimer } from "./TimerCountdown";
import { Button } from "./Button";
import { Calendar, Users, MapPin, Clock } from "lucide-react";
import { Progress } from "antd";

export function CardJPO({ jpo, occupationPercentage = 0 }) {
  // Création d'une Date combinée avec heure optionnelle
  const createDateTime = (dateStr, timeStr) => {
    const date = new Date(dateStr);
    if (!timeStr) return date;
    const [hours, minutes] = timeStr.split(":").map(Number);
    date.setHours(hours ?? 0, minutes ?? 0, 0, 0);
    return date;
  };

  const openingHour = "09:00";
  const targetDateWithTime = createDateTime(jpo.date, openingHour);
  const maxCapacity = jpo.max_capacity || 1;
  const currentRegistrations = Math.round((occupationPercentage / 100) * maxCapacity);

  const getProgressStatus = (percentage) => {
    if (percentage >= 100) return "exception";
    if (percentage >= 80) return "active";
    return "normal";
  };

  const getProgressColor = (percentage) => {
    if (percentage >= 100) {
      return {
        '0%': '#ff4d4f',
        '100%': '#ff7875',
      };
    }
    if (percentage >= 80) {
      return {
        '0%': '#faad14',
        '100%': '#ffd666',
      };
    }
    return {
      '0%': '#108ee9',
      '100%': '#87d068',
    };
  };

  return (
    <div className="md:w-[60%] w-[90%] mx-auto mb-10 bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 relative z-0">
      <div className="flex flex-col md:flex-row">

        {/* Image JPO + Indicateurs d'occupation */}
        <div className="md:w-1/2 h-48 md:h-auto relative">
          <img
            src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
            alt={`Campus universitaire pour JPO ${jpo.name}`}
            className="w-full h-full object-cover"
          />

          <div className={`absolute bottom-3 right-3 px-3 py-1 rounded-full text-xs font-bold text-black shadow-lg bg-white z-10`}>
            {occupationPercentage}% rempli
          </div>

          <div className={`absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold text-white shadow-lg z-10 ${
            occupationPercentage >= 100 
              ? 'bg-red-700' 
              : occupationPercentage >= 80 
                ? 'bg-orange-700' 
                : 'bg-green-400'
          }`}>
            {occupationPercentage >= 100 
              ? '🔴 COMPLET' 
              : occupationPercentage >= 80 
                ? '🟠 BIENTÔT COMPLET' 
                : '🟢 DISPONIBLE'
            }
          </div>
        </div>

        {/* Contenu principal */}
        <div className="lg:w-3/5 bg-gradient-to-br from-[#0062FF] via-[#0052CC] to-[#0041AA] text-white p-4 flex flex-col justify-between relative overflow-hidden">
          <div className="space-y-2">
            <div>
              <h2 className="text-lg lg:text-xl font-bold mb-1 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                {jpo.name}
              </h2>
              <div className="h-0.5 w-10 bg-gradient-to-r from-yellow-400 to-pink-400 rounded-full"></div>
            </div>

            <div className="w-full min-w-0 bg-white/5 rounded-lg border border-white/10 flex-1 p-2">
              <CountdownTimer targetDate={targetDateWithTime} />
            </div>

            <div className="flex items-center justify-between gap-4 mt-3">
              <div className="flex-1">
                <div className="flex items-end flex-col-reverse">
                  <Progress 
                    type="line" 
                    percent={occupationPercentage} 
                    status={getProgressStatus(occupationPercentage)}
                    strokeColor={getProgressColor(occupationPercentage)}
                    showInfo={false}
                  />
                  <div className="flex flex-row justify-between items-center w-full mt-2">
                    <div className="flex flex-row gap-2 items-center mt-2">
                      <Users className="w-4"/>
                      <p className="text-xs text-white">Inscriptions</p>
                    </div>

                    <div className="text-xs font-semibold flex bg-[#0062FF] rounded-xl pr-[10px] pl-[10px] pt-[2px] pb-[2px]">
                      <p className="text-white">
                        {currentRegistrations} / {maxCapacity}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Infos date, heure, campus */}
            <div className="flex flex-col md:flex-row items-start md:items-center gap-1 mt-2 flex-wrap">
              <div className="flex items-center justify-between gap-3 sm:w-full w-full md:w-auto">
                <div className="flex items-center gap-2 bg-white/5 rounded-lg p-2 border border-white/10 flex-1">
                  <div className="bg-white/20 p-1 rounded">
                    <Calendar className="w-3 h-3" />
                  </div>
                  <div>
                    <p className="text-xs text-white/70">Date</p>
                    <p className="font-semibold text-xs">
                      {new Date(jpo.date).toLocaleDateString("fr-FR", {
                        day: "2-digit",
                        month: "short",
                      })}
                    </p>
                  </div>
                </div>
              </div>

              <div className="flex items-center gap-1 bg-white/5 rounded-lg p-2 border border-white/10 w-full md:w-auto">
                <div className="bg-white/20 p-1 rounded">
                  <Clock className="w-3 h-3" />
                </div>
                <div>
                  <p className="text-xs text-white/70">Ouverture</p>
                  <p className="font-bold text-xs">{openingHour.replace(':', 'h')}</p>
                </div>
              </div>

              <div className="flex items-center gap-1 bg-white/5 rounded-lg p-2 border border-white/10 w-full md:w-auto">
                <div className="bg-white/20 p-1 rounded">
                  <MapPin className="w-3 h-3" />
                </div>
                <div>
                  <p className="text-xs text-white/70">Campus</p>
                  <p className="font-bold text-xs">{jpo.campus_id}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Bouton détails */}
          <div className="flex justify-center md:justify-end">            
            <Button 
              to={`/jpo:id`} 
              label="Voir les détails" 
              className="w-full md:w-auto"
              aria-label={`Voir les détails de la JPO ${jpo.name}`}
            />
          </div>
        </div>
      </div>
    </div>
  );
}
