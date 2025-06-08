export function Input({ label, value, className = "" }) {
  return (
    <div className={`space-y-2 ${className}`}>
      <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">{label}</label>
      <div className="bg-gray-100 rounded-lg p-4 border-l-8 border-[#0062FF]">
        <p className="text-gray-800 font-medium text-lg">{value}</p>
      </div>
    </div>
  );
}
