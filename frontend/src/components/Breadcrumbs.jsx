import { Link, useLocation } from "react-router-dom";
import { ChevronRight } from "lucide-react";

export default function Breadcrumbs() {
  const location = useLocation();

  const segments = location.pathname.split("/").filter(Boolean);

  const cleanedSegments = segments[0] === "admindashboard" ? segments.slice(1) : segments;

  const paths = cleanedSegments.map((_, i) =>
    "/admindashboard/" + cleanedSegments.slice(0, i + 1).join("/")
  );

  return (
    <nav className="breadcrumbs text-sm text-black">
      <ul className="flex items-center gap-2">
        <li className="flex items-center gap-2">
          <Link to="/admindashboard" className="hover:underline">Dashboard</Link>
          {cleanedSegments.length > 0 && <ChevronRight className="w-4 h-4" />}
        </li>

        {cleanedSegments.map((segment, index) => {
          const isLast = index === cleanedSegments.length - 1;
          const path = paths[index];

          return (
            <li key={index} className="flex items-center gap-2 capitalize">
              {!isLast ? (
                <>
                  <Link to={path} className="hover:underline">{segment}</Link>
                  <ChevronRight className="w-4 h-4" />
                </>
              ) : (
                <span className="text-gray-500">{segment}</span>
              )}
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
