Entity Framework.

Create Data Model.
	-Course<->Enrollment<->Student
		Course to Enrollment One-to-many.
		Student to Enrollment One-to-many.

In Model Folder.
Create File Student.cs
using System;
using System.Collections.Generic;

Create File Student.cs
namespace ContosoUniversity.Models
{
    public class Student
    {
        public int ID { get; set; }
        public string LastName { get; set; }
        public string FirstMidName { get; set; }
        public DateTime EnrollmentDate { get; set; }
        
        public virtual ICollection<Enrollment> Enrollments { get; set; }
    }
}


Create File Enrollment.cs
namespace ContosoUniversity.Models
{
    public enum Grade
    {
        A, B, C, D, F
    }

    public class Enrollment
    {
        public int EnrollmentID { get; set; }
        public int CourseID { get; set; }
        public int StudentID { get; set; }
        public Grade? Grade { get; set; }
        
        public virtual Course Course { get; set; }
        public virtual Student Student { get; set; }
    }
}

Course.cs

using System.Collections.Generic;
using System.ComponentModel.DataAnnotations.Schema;

namespace ContosoUniversity.Models
{
    public class Course
    {
        [DatabaseGenerated(DatabaseGeneratedOption.None)]
        public int CourseID { get; set; }
        public string Title { get; set; }
        public int Credits { get; set; }
        
        public virtual ICollection<Enrollment> Enrollments { get; set; }
    }
}