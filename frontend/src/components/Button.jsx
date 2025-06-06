import { useNavigate } from "react-router-dom";

export default function Button({
  to = "/events",
  label = "S'inscrire",
  className = ""
}) {
  const navigate = useNavigate();

  const baseClass = "text-md bg-white text-[#0062F0] px-5 py-2 rounded-full hover:bg-[#d6d6d6] sm:text-2xl font-semibold text-white h-14 sm:h-14 w-full sm:w-52 rounded-full transition"

  return (
    <button onClick={() => navigate(to)} className={`${baseClass} ${className}`}>
      {label}
    </button>
  );
}
