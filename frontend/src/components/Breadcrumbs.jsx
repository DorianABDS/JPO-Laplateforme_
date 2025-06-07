import { Link, useLocation } from "react-router-dom";
import { ChevronRight } from "lucide-react";

function capitalizeWords(str) {
  // Remplace tirets par espaces, puis majuscule à chaque mot
  return str
    .replace(/-/g, " ")
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function Breadcrumbs() {
  const location = useLocation();

  const segments = location.pathname.split("/").filter(Boolean);

  // Si la première partie est "admindashboard", on l’enlève pour afficher les sous-chemins
  const cleanedSegments = segments[0] === "admindashboard" ? segments.slice(1) : segments;

  // Construit les chemins complets pour chaque segment sous /admindashboard/
  const paths = cleanedSegments.map((_, i) =>
    "/admindashboard/" + cleanedSegments.slice(0, i + 1).join("/")
  );

  return (
    <nav className="breadcrumbs text-sm text-black" aria-label="Breadcrumb">
      <ul className="flex items-center gap-2">
        <li className="flex items-center gap-2">
          <Link to="/admindashboard" className="hover:underline">Dashboard</Link>
          {cleanedSegments.length > 0 && <ChevronRight className="w-4 h-4" />}
        </li>

        {cleanedSegments.map((segment, index) => {
          const isLast = index === cleanedSegments.length - 1;
          const path = paths[index];
          const label = capitalizeWords(segment);

          return (
            <li key={index} className="flex items-center gap-2 capitalize">
              {!isLast ? (
                <>
                  <Link to={path} className="hover:underline">{label}</Link>
                  <ChevronRight className="w-4 h-4" />
                </>
              ) : (
                <span className="text-gray-500" aria-current="page">{label}</span>
              )}
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
